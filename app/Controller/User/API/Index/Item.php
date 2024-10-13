<?php
declare (strict_types=1);

namespace App\Controller\User\API\Index;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Visitor;
use App\Interceptor\Waf;
use App\Model\ItemSku;
use App\Model\Site;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, Visitor::class], type: Interceptor::API)]
class Item extends Base
{

    #[Inject]
    private \App\Service\User\Item $item;

    #[Inject]
    private \App\Service\User\Order $order;


    /**
     * @return Response
     * @throws RuntimeException
     * @throws NotFoundException
     */
    public function list(): Response
    {
        $categoryId = $this->request->post("category_id", Filter::INTEGER) ?? null;
        return $this->json(200, "success", $this->item->list($this->getUser(),
            $categoryId, Site::getUser(
                (string)$this->request->header("Host")
            )));
    }


    /**
     * @return Response
     * @throws NotFoundException
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Item::class, "itemId"]
    ])]
    public function detail(): Response
    {
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        $item = $this->item->getItem($this->getUser(), $itemId, Site::getUser((string)$this->request->header("Host")));
        return $this->json(200, "success ", $item->toArray());
    }


    /**
     * @throws JSONException|RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Item::class, ["skuId", "quantity"]]
    ])]
    public function getPrice(): Response
    {
        $skuId = $this->request->post("sku_id", Filter::INTEGER);
        $quantity = $this->request->post("quantity", Filter::INTEGER);

        if ($quantity <= 0) {
            throw new JSONException("购买数量不能低于1个");
        }

        $sku = ItemSku::with(["item"])->find($skuId);

        if (!$sku) {
            throw new JSONException("SKU不存在");
        }

        if ($sku->item->status != 1) {
            throw new JSONException("该商品未上架");
        }

        $amount = $this->order->getAmount($this->getUser(), $sku, $quantity);
        return $this->json(data: ["amount" => $amount]);
    }


}