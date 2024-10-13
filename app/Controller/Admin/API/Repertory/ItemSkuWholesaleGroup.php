<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryItemSkuWholesale;
use App\Model\RepertoryItemSkuWholesaleGroup as Model;
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
class ItemSkuWholesaleGroup extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @param int $wholesaleId
     * @return Response
     * @throws RuntimeException
     */
    public function get(int $wholesaleId): Response
    {
        $columns = [
            'stock_price',
            'status'
        ];
        $data = UserGroup::query()->orderBy("sort", "asc")->with(['repertoryItemSkuWholesaleGroup' => function (Relation $relation) use ($wholesaleId) {
            $relation->where("wholesale_id", $wholesaleId);
        }])->get(['id', 'name', 'sort', 'icon'])->toArray();

        foreach ($data as $k => $v) {
            if ($v['repertory_item_sku_wholesale_group']) {
                foreach ($columns as $column) {
                    $data[$k][$column] = $v['repertory_item_sku_wholesale_group'][$column];
                }
                unset($data[$k]['repertory_item_sku_wholesale_group']);
            }
        }

        return $this->json(data: ['list' => $data]);
    }

    /**
     * @param int $wholesaleId
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        ['key' => 'stock_price', 'rule' => 'notZero', 'message' => ['notZero' => '进货价，必须大于0']],
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


            $model = Model::query()->where("wholesale_id", $wholesaleId)->where("group_id", $map['id'])->first();

            if (!$model) {
                $model = new Model();
                $model->group_id = $map['id'];
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