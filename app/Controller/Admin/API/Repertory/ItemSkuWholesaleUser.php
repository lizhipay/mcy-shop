<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryItemSkuWholesale;
use App\Model\RepertoryItemSkuWholesaleUser as Model;
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
class ItemSkuWholesaleUser extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @param int $wholesaleId
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(int $wholesaleId): Response
    {
        $columns = [
            'stock_price',
            'status'
        ];

        $get = new Get(User::class);
        $get->setWhere($this->request->post());
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));

        $get->setColumn('id', 'username', 'avatar');

        $data = $this->query->get($get, function (Builder $builder) use ($wholesaleId) {
            return $builder->with(['repertoryItemSkuWholesaleUser' => function (Relation $relation) use ($wholesaleId) {
                $relation->where("wholesale_id", $wholesaleId);
            }]);
        });

        foreach ($data['list'] as $k => $v) {
            if ($v['repertory_item_sku_wholesale_user']) {
                foreach ($columns as $column) {
                    $data['list'][$k][$column] = $v['repertory_item_sku_wholesale_user'][$column];
                }
                unset($data['list'][$k]['repertory_item_sku_wholesale_user']);
            }
        }
        return $this->json(data: $data);
    }


    /**
     * @param int $wholesaleId
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\ItemSku::class, "stockPrice"]
    ])]
    public function save(int $wholesaleId): Response
    {
        $map = $this->request->post();
        try {
            /**
             * @var RepertoryItemSkuWholesale $repertoryItemSkuWholesale
             */
            $repertoryItemSkuWholesale = RepertoryItemSkuWholesale::query()->find($wholesaleId);

            if (!$repertoryItemSkuWholesale) {
                throw new JSONException("批发配置不存在");
            }


            $model = Model::query()->where("wholesale_id", $wholesaleId)->where("customer_id", $map['id'])->first();

            if (!$model) {
                $model = new Model();
                $model->customer_id = $map['id'];
                $model->wholesale_id = $wholesaleId;
                $model->create_time = Date::current();
                $repertoryItemSkuWholesale->user_id && $model->user_id = $repertoryItemSkuWholesale->user_id;
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