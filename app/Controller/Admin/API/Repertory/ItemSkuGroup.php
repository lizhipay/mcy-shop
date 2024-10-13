<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryItemSkuGroup as Model;
use App\Model\UserGroup;
use App\Service\Common\Query;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class ItemSkuGroup extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @param string $id
     * @param string $type
     * @return Response
     * @throws RuntimeException
     */
    public function get(string $id, string $type): Response
    {
        $columns = [
            'stock_price',
            'market_control_status',
            'market_control_min_price',
            'market_control_max_price',
            'market_control_level_min_price',
            'market_control_level_max_price',
            'market_control_user_min_price',
            'market_control_user_max_price',
            'market_control_min_num',
            'market_control_max_num',
            'market_control_only_num',
            'status'
        ];
        $data = UserGroup::query()->orderBy("sort", "asc")->with(['repertoryItemSkuGroup' => function (Relation $relation) use ($id, $type) {
            $relation->where($type == "real" ? "sku_id" : "temp_id", $id);
        }])->get(['id', 'name', 'sort', 'icon'])->toArray();

        foreach ($data as $k => $v) {
            if ($v['repertory_item_sku_group']) {
                foreach ($columns as $column) {
                    $data[$k][$column] = $v['repertory_item_sku_group'][$column];
                }
                unset($data[$k]['repertory_item_sku_group']);
            }
        }

        return $this->json(data: ['list' => $data]);
    }


    /**
     * @param string $skuId
     * @param string $type
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        ['key' => 'stock_price', 'rule' => 'notZero', 'message' => ['notZero' => '进货价，必须大于0']],
    ])]
    public function save(string $skuId, string $type): Response
    {
        $map = $this->request->post();
        try {
            $field = $type == "real" ? "sku_id" : "temp_id";
            $model = Model::query()->where($field, $skuId)->where("group_id", $map['id'])->first();

            if (!$model) {
                $model = new Model();
                $model->group_id = $map['id'];
                $model->$field = $skuId;
                $model->create_time = Date::current();
            }

            foreach ($map as $k => $v) {
                if ($k != "id") {
                    $model->$k = $v;
                }
            }

            $model->save();
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }
}