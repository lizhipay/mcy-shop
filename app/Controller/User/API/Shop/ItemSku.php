<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\ItemSku as Model;
use App\Service\Common\Query;
use App\Service\Common\RepertoryItemSku;
use App\Service\User\Ownership;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Decimal;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class ItemSku extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private RepertoryItemSku $repertoryItemSku;

    #[Inject]
    private Ownership $ownership;

    /**
     * @param string $id
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(string $id): Response
    {
        $get = new Get(Model::class);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("sort", "asc",);
        $data = $this->query->get($get, function (Builder $builder) use ($id) {
            return $builder->where("item_id", $id)->where("user_id", $this->getUser()->id);
        });
        foreach ($data['list'] as &$value) {
            $value['sku_entity'] = $this->repertoryItemSku->getSKUEntity((int)$value['repertory_item_sku_id'], (int)$value['user_id']);
        }
        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    public function save(): Response
    {
        $map = $this->request->post();


        $itemSku = \App\Model\ItemSku::query()->where("user_id", $this->getUser()->id)->find($map['id']);
        if (!$itemSku) {
            throw new JSONException("权限不足");
        }

        $SKUEntity = $this->repertoryItemSku->getSKUEntity($itemSku->repertory_item_sku_id, $this->getUser()->id);


        if (isset($map['dividend_amount']) && isset($map['price'])) {
            if ((new Decimal($map['price'], 6))->sub($SKUEntity->stockPrice)->sub($map['dividend_amount'])->getAmount(6) <= 0) {
                throw new JSONException("分红价格太低，无法盈利");
            }
        }

        if ($SKUEntity->marketControl && isset($map['price'])) {
            if ($SKUEntity->marketControlMinPrice > 0 && $SKUEntity->marketControlMinPrice > $map['price']) {
                throw new JSONException("零售价不得低于控价范围");
            }
            if ($SKUEntity->marketControlMaxPrice > 0 && $SKUEntity->marketControlMaxPrice < $map['price']) {
                throw new JSONException("零售价不得高于控价范围");
            }
        }

        $save = new Save(Model::class);
        $save->disableAddable();
        $save->setMap($map, ["name", "picture_thumb_url", "picture_url", "price", "sort", "private_display", "dividend_amount"]);
        $save->addForceMap("user_id", $this->getUser()->id);
        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }

}