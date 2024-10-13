<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Pay;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\PayGroup as Model;
use App\Model\UserGroup;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class PayGroup extends Base
{

    /**
     * @param string $id
     * @param string $type
     * @return Response
     * @throws RuntimeException
     */
    public function get(string $id, string $type): Response
    {
        $columns = ["fee", "status"];
        $data = UserGroup::query()->orderBy("sort", "asc")->with(['payGroup' => function (Relation $relation) use ($id, $type) {
            $relation->where($type == "real" ? "pay_id" : "temp_id", $id);
        }])->get(['id', 'name', 'sort', 'icon'])->toArray();
        foreach ($data as &$v) {
            if ($v['pay_group']) {
                foreach ($columns as $column) {
                    $v[$column] = $v['pay_group'][$column];
                }
                unset($v['pay_group']);
            }
        }
        return $this->json(data: ['list' => $data]);
    }


    /**
     * @param string $payId
     * @param string $type
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function save(string $payId, string $type): Response
    {
        $map = $this->request->post();
        try {
            $field = $type == "real" ? "pay_id" : "temp_id";
            $model = Model::query()->where($field, $payId)->where("group_id", $map['id'])->first();
            if (!$model) {
                $model = new Model();
                $model->group_id = $map['id'];
                $model->$field = $payId;
                $model->create_time = Date::current();
            }
            foreach ($map as $k => $v) {
                if ($k != "id") {
                    $model->$k = $v;
                }
            }
            $model->save();
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->json();
    }
}