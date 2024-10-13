<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryItemSkuUser as Model;
use App\Model\User;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class ItemSkuUser extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @param string $id
     * @param string $type
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
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

        $get = new Get(User::class);
        $get->setWhere($this->request->post());
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setColumn('id', 'username', 'avatar');

        $data = $this->query->get($get, function (Builder $builder) use ($type, $id) {
            return $builder->with(['repertoryItemSkuUser' => function (Relation $relation) use ($id, $type) {
                $relation->where($type == "real" ? "sku_id" : "temp_id", $id);
            }]);
        });

        foreach ($data['list'] as $k => $v) {
            if ($v['repertory_item_sku_user']) {
                foreach ($columns as $column) {
                    $data['list'][$k][$column] = $v['repertory_item_sku_user'][$column];
                }
                unset($data['list'][$k]['repertory_item_sku_user']);
            }
        }

        return $this->json(data: $data);
    }


    /**
     * @param string $skuId
     * @param string $type
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\ItemSku::class, "stockPrice"]
    ])]
    public function save(string $skuId, string $type): Response
    {
        $map = $this->request->post();
        try {
            $field = $type == "real" ? "sku_id" : "temp_id";
            $model = Model::query()->where($field, $skuId)->where("customer_id", $map['id'])->first();
            if (!$model) {
                $model = new Model();
                $model->customer_id = $map['id'];
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