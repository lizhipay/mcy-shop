<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Const\MarketControl;
use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Waf;
use App\Model\ItemSkuUser as Model;
use App\Model\User;
use App\Service\Common\Query;
use App\Service\Common\RepertoryItemSku;
use App\Service\User\Ownership;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;

#[Interceptor(class: [PostDecrypt::class, Waf::class, \App\Interceptor\User::class, Merchant::class], type: Interceptor::API)]
class ItemSkuUser extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private Ownership $ownership;

    #[Inject]
    private RepertoryItemSku $repertoryItemSku;

    /**
     * @param int $id
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(int $id): Response
    {
        $sku = \App\Model\ItemSku::find($id);
        if (!$sku) {
            throw new JSONException("SKU不存在");
        }

        $post = $this->request->post();
        $get = new Get(User::class);
        $get->setWhere($post);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy(...$this->query->getOrderBy($post, "id", "desc"));
        $get->setColumn('id', 'username', 'avatar');
        $data = $this->query->get($get, function (Builder $builder) use ($id) {
            $builder = $builder->where("pid", $this->getUser()->id);
            return $builder->with(['itemSkuUser' => function (Relation $relation) use ($id) {
                $relation->where("sku_id", $id)->select(['id', 'price', 'status', 'customer_id', 'dividend_amount']);
            }]);
        });

        foreach ($data['list'] as &$item) {
            $item['sku_entity'] = $this->repertoryItemSku->getSKUEntity($sku->repertory_item_sku_id, $this->getUser()->id);
        }
        return $this->json(data: $data);
    }


    /**
     * @param int $skuId
     * @return Response
     * @throws JSONException|\ReflectionException
     */
    public function save(int $skuId): Response
    {
        $map = $this->request->post();

        //验证归属权
        $this->ownership->throw(
            $this->ownership->ownMember($this->getUser()->id, (int)$map['id'])
        );

        $itemSku = \App\Model\ItemSku::query()->where("user_id", $this->getUser()->id)->find($skuId);
        if (!$itemSku) {
            throw new JSONException("权限不足");
        }

        if (isset($map['item_sku_user.price'])) {
            $this->repertoryItemSku->marketControlCheck((string)$map['item_sku_user.price'], $itemSku->repertory_item_sku_id, $this->getUser()->id, MarketControl::TYPE_USER);
        }

        try {
            $model = Model::query()->where("user_id", $this->getUser()->id)->where("sku_id", $skuId)->where("customer_id", $map['id'])->first();
            if (!$model) {
                $model = new Model();
                $model->customer_id = $map['id'];
                $model->sku_id = $skuId;
                $model->create_time = Date::current();
                $model->user_id = $this->getUser()->id;
            }
            foreach ($map as $k => $v) {
                $k = strtolower(trim($k));
                if (in_array($k, ["item_sku_user.price", "item_sku_user.status", "item_sku_user.dividend_amount"])) {
                    $model->$k = $v;
                }
            }
            $model->save();
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }
}