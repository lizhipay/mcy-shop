<?php
declare (strict_types=1);

namespace App\Controller\User\API\Index;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Visitor;
use App\Interceptor\Waf;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Validator\Method;
use Kernel\Waf\Filter;

#[
    Interceptor(class: [PostDecrypt::class, Waf::class, Visitor::class], type: Interceptor::API),
    Validator([[Common::class, "clientId"]], Method::COOKIE)
]
class Cart extends Base
{
    #[Inject]
    private \App\Service\User\Cart $cart;


    /**
     * @return string
     */
    private function getClientId(): string
    {
        $clientId = $this->cart->getClientId($this->getUser(), (string)$this->request->cookie("client_id"));
        $this->response->withCookie("client_id", $clientId, 31536000);
        return $clientId;
    }

    /**
     * @return Response
     * @throws RuntimeException
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\User\Cart::class, ["skuId", "quantity"]]
    ])]
    public function add(): Response
    {
        $post = $this->request->post(); //option
        $quantity = $this->request->post("quantity", Filter::INTEGER);
        $skuId = $this->request->post("sku_id", Filter::INTEGER);
        $add = $this->cart->add($this->getUser(), $this->getClientId(), $quantity, $skuId, $post);
        if (!$add) {
            throw new JSONException("添加失败");
        }
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function items(): Response
    {
        $items = $this->cart->getItems($this->getUser(), $this->getClientId());
        return $this->json(data: $items);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function getAmount(): Response
    {
        return $this->json(data: ['amount' => $this->cart->getAmount($this->getUser(), $this->getClientId())]);
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Cart::class, ["itemId", "quantity"]]
    ])]
    public function changeQuantity(): Response
    {
        $quantity = $this->request->post("quantity", Filter::INTEGER);
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        $this->cart->changeQuantity($this->getUser(), $this->getClientId(), $itemId, $quantity);
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Cart::class, "itemId"]
    ], Method::GET)]
    public function updateOption(): Response
    {
        $itemId = $this->request->get("item_id", Filter::INTEGER);
        $this->cart->updateOption($this->getUser(), $this->getClientId(), $itemId, $this->request->post());
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Cart::class, "itemId"]
    ])]
    public function getItem(): Response
    {
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        return $this->json(data: $this->cart->getItem($this->getUser(), $this->getClientId(), $itemId)->toArray());
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Cart::class, "itemId"]
    ])]
    public function delItem(): Response
    {
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        $status = $this->cart->del($this->getUser(), $this->getClientId(), $itemId);
        return $this->json(data: ['status' => $status]);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function clear(): Response
    {
        $this->cart->clear($this->getUser(), $this->getClientId());
        return $this->json();
    }
}