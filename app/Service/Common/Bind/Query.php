<?php /** @noinspection PhpUndefinedMethodInspection */
declare(strict_types=1);

namespace App\Service\Common\Bind;

use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use Hyperf\Database\Model\Builder;
use Kernel\Container\Di;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Date;

class Query implements \App\Service\Common\Query
{

    /**
     * 禁止作为查询条件（WHERE）的敏感列。
     * user/manage 表的 password 同时是 JWT(HS256) 签名密钥、salt 为加盐材料，
     * 若允许作为过滤列，攻击者可通过 total 计数做条件盲注逐字符提取哈希 → 伪造令牌。
     */
    private const SENSITIVE_COLUMNS = ['password', 'salt'];


    /**
     * @param string $model
     * @return string
     * @throws \ReflectionException
     */
    private function getTable(string $model): string
    {
        $instance = Di::instance()->make($model);
        return $instance->getTable();
    }

    /**
     * @param mixed $query
     * @param string $type
     * @param string $column
     * @param string $val
     * @return void
     */
    private function setWhere(mixed &$query, string $type, string $column, string $val): void
    {
        switch ($type) {
            case "equal":
                $query = $query->where($column, $val);
                break;
            case "betweenStart":
                $query = $query->where($column, ">=", $val);
                break;
            case "betweenEnd":
                $query = $query->where($column, "<=", $val);
                break;
            case "search":
                $query = $query->where($column, "like", '%' . $val . '%');
                break;
        }
    }

    /**
     * @param Get $get
     * @param callable|null $append
     * @param int $resultType
     * @return mixed
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function get(Get $get, ?callable $append = null, int $resultType = self::RESULT_TYPE_ARRAY): mixed
    {
        /**
         * @var Builder $query
         */
        $query = $get->model::query();
        $tableName = $this->getTable($get->model);


        if (count($get->leftJoinWhere) > 0) {
            $get->orderBy[0] = "{$tableName}.{$get->orderBy[0]}";
            if ($get->columns === ["*"]) {
                $get->columns = ["{$tableName}.*"];
            } else {
                foreach ($get->columns as $index => $column) {
                    $get->columns[$index] = "{$tableName}.{$column}";
                }
            }
        }

        foreach ($get->where as $key => $val) {
            if (!is_scalar($val)) {
                continue; //跳过非标量 where 值（如 status[]=1），避免下游 setWhere(string) 触发 TypeError 造成 500 DoS
            }
            $val = urldecode((string)$val);
            $key = urldecode($key);
            $args = explode('-', $key);
            if ($val === '') {
                continue;
            }
            if (count($args) != 2) {
                continue;
            }
            $type = $args[0];
            $column = $args[1];

            // 列名只允许字母、数字、下划线，防止列名注入与基于 SQL 报错的探测
            if (!preg_match('/^[A-Za-z0-9_]+$/', (string)$column)) {
                continue;
            }

            // 敏感列禁止作为查询条件，杜绝条件盲注提取 password/salt（= JWT 签名密钥）
            if (in_array(strtolower((string)$column), self::SENSITIVE_COLUMNS, true)) {
                throw new JSONException("非法的查询条件");
            }

            foreach ($get->leftJoinWhere as $jn) {
                $relatedTableName = $this->getTable($jn['related']);
                foreach ($jn['columns'] as $k => $v) {
                    if ($column == $k) {
                        $query = $query->leftJoin($relatedTableName, "{$relatedTableName}.{$jn['foreignKey']}", "=", "{$tableName}.{$jn["localKey"]}");
                        $this->setWhere($query, $type, "{$relatedTableName}.{$v}", $val);
                        continue 3;
                    }
                }
            }

            $this->setWhere($query, $type, $tableName . "." . $column, $val);
        }

        //追加执行
        if (is_callable($append)) {
            $query = call_user_func($append, $query);
        }


        $query = $query->orderBy($get->orderBy[0], $get->orderBy[1])->distinct();

        Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_QUERY_GET_BEFORE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $get, $query);
        if ($get->paginate) {
            $paginate = $query->paginate($get->paginate[1], $get->columns, '', $get->paginate[0]);
            Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_QUERY_GET_RESULT, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $paginate, $query);
            if ($resultType === \App\Service\Common\Query::RESULT_TYPE_ARRAY) {
                $paginate = $paginate->toArray();
                return ["list" => $paginate['data'], "total" => $paginate['total']];
            }
            return $paginate;
        }

        $result = $query->get($get->columns);
        Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_QUERY_GET_RESULT, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $result, $query);
        if ($resultType === \App\Service\Common\Query::RESULT_TYPE_ARRAY) {
            return $result->toArray();
        }
        return $result;
    }


    /**
     * @param Save $save
     * @return mixed
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function save(Save $save): mixed
    {
        /**
         * @var Builder $query
         */
        $query = $save->model::query();

        // 附加归属/作用域约束（如 user_id = 当前用户），杜绝按裸 id 跨租户接管
        foreach ($save->where as $where) {
            $query = $query->where($where[0], $where[1]);
        }

        $model = $save->id ? $query->find($save->id) : null;
        $modify = false;

        // 客户端提供了 id 且设置了归属约束，却查不到记录：视为无权修改，拒绝并且不静默新增
        if ($save->id && !$model && count($save->where) > 0) {
            throw new RuntimeException("数据不存在或无权修改");
        }

        if (!$model) {
            if (!$save->isAddable) {
                throw new RuntimeException("禁止新增");
            }
            $model = new $save->model;
            $save->isAddCreateTime && ($model->create_time = Date::current());
        } else {
            if (!$save->isModifiable) {
                throw new RuntimeException("禁止修改");
            }
            $modify = true;
        }

        $middles = [];

        /**
         * @param string $key
         * @param mixed $value
         * @param array $middles
         * @param mixed $model
         * @param Save $save
         * @return void
         */
        $addColumn = function (string $key, mixed $value, array &$middles, mixed &$model, Save $save) {
            $middle = $save->getMiddle($key);
            if ($middle) {
                $middles[] = ['middle' => $middle, 'data' => $value];
            } else {
                // if (is_scalar($value) && !is_integer($value)) {
                //    $value = urldecode($value);
                // }
                $model->$key = $value;
            }
        };

        foreach ($save->map as $key => $item) {
            if ($modify) {
                if (count($save->modifiableWhitelist) > 0 && !in_array($key, $save->modifiableWhitelist)) {
                    continue;
                }
            } else {
                if (count($save->addWhitelist) > 0 && !in_array($key, $save->addWhitelist)) {
                    continue;
                }
            }
            $addColumn($key, $item, $middles, $model, $save);
        }

        foreach ($save->forceMap as $key => $item) {
            $addColumn($key, $item, $middles, $model, $save);
        }

        Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_QUERY_SAVE_BEFORE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $save, $model);
        $model->save();
        $id = $model->id;
        Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_QUERY_SAVE_AFTER, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $model);
        foreach ($middles as $m) {
            $middle = $m['middle'];
            $data = $m['data'];
            if (!empty($data)) {
                //删除中间表关系
                $middle['middle']::query()->where($middle['localKey'], $id)->delete();
            }
            $localKey = $middle['localKey'];
            $foreignKey = $middle['foreignKey'];
            //重新建立模型关系
            foreach ($data as $datum) {
                $middleObject = new $middle['middle'];
                $middleObject->$localKey = $id;
                $middleObject->$foreignKey = $datum;
                $middleObject->save();
            }
        }

        return $model;
    }

    /**
     * @param Delete $delete
     * @return int
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function delete(Delete $delete): int
    {
        if (count($delete->list) === 0) {
            throw new JSONException("你还没有选择数据呢(◡ᴗ◡✿)");
        }

        $count = 0;
        foreach ($delete->list as $id) {
            /**
             * @var Builder $query
             */
            $query = $delete->model::query();
            foreach ($delete->where as $where) {
                $query = $query->where(...$where);
            }

            Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_QUERY_DELETE_BEFORE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $id, $query);
            if ($query->where("id", $id)->first()?->delete()) {
                $count++;
                Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_QUERY_DELETE_SUCCESS, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $id, $query);
            } else {
                Plugin::instance()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_QUERY_DELETE_ERROR, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $id, $query);
            }
        }

        return $count;
    }

    /**
     * @param array $map
     * @param string $field
     * @param string $rule
     * @return array
     */
    public function getOrderBy(array $map, string $field, string $rule = 'desc'): array
    {
        if (isset($map['sort_field']) && isset($map['sort_rule'])
            && is_string($map['sort_field']) && is_string($map['sort_rule'])) {
            $sortField = $map['sort_field'];
            $sortRule = strtolower($map['sort_rule']);
            //排序字段同样施加列名白名单 + 敏感列(password/salt)拦截，杜绝按敏感列排序造成的信息泄漏
            if (preg_match('/^[A-Za-z0-9_]+$/', $sortField)
                && !in_array(strtolower($sortField), self::SENSITIVE_COLUMNS, true)
                && in_array($sortRule, ['asc', 'desc'], true)) {
                return [$sortField, $sortRule];
            }
        }
        return [$field, $rule];
    }
}