<?php
declare (strict_types=1);

namespace App\Controller\User;

use App\Interceptor\Visitor;
use App\Interceptor\Waf;
use App\Service\User\Cart;
use App\Service\User\Category;
use App\Service\User\Item;
use App\Service\User\Order;
use App\Service\User\Pay;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\ViewException;
use Kernel\Plugin\Const\Theme;
use Kernel\Util\UserAgent;
use Kernel\Validator\Method;


#[Interceptor(class: [Waf::class, Visitor::class], type: Interceptor::VIEW)]
class Index extends Base
{
    #[Inject]
    private Category $category;

    #[Inject]
    private Item $item;

    #[Inject]
    private Order $order;

    #[Inject]
    private Pay $pay;


    #[Inject]
    private Cart $cart;


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
     * @return array
     * @throws NotFoundException
     */
    public function getCategory(): array
    {
        return $this->category->only($this->getSiteOwner());
    }


    /**
     * @return Response
     * @throws NotFoundException
     */
    public function index(): Response
    {
        $keywords = $this->request->get("keywords");
        return $this->theme(Theme::INDEX, "Index/Home.html", "店铺首页", [
            "category" => $this->getCategory(),
            "keywords" => $keywords
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws ViewException
     * @throws NotFoundException
     */
    #[Validator([
        [\App\Validator\User\Index::class, "id"]
    ], Method::GET, Interceptor::VIEW)]
    public function item(int $id): Response
    {
        try {
            $item = $this->item->getItem($this->getUser(), $id, $this->getSiteOwner());
        } catch (JSONException $e) {
            throw new ViewException($e->getMessage(), $e->getCode());
        }

        return $this->theme(Theme::ITEM, "Index/Item.html", "商品详细页", ["category" => $this->getCategory(), "item" => $item->toArray()]);
    }


    /**
     * @param string $tradeNo
     * @return Response
     * @throws NotFoundException
     */
    #[Validator([
        [\App\Validator\User\Index::class, "tradeNo"]
    ], Method::GET, Interceptor::VIEW)]
    public function checkout(string $tradeNo): Response
    {
        $order = $this->order->getCheckoutOrder($tradeNo);
        if ($order->status == 1) {
            return $this->response->redirect("/pay/sync." . $tradeNo);
        }
        $pay = $this->pay->getList(UserAgent::getEquipment($this->request->header("UserAgent")), "product", $this->getSiteOwner(), $order->totalAmount);
        return $this->theme(Theme::CHECKOUT, "Index/Checkout.html", "结账", ["category" => $this->getCategory(), "order" => $order->toArray(), 'pay' => $pay]);
    }


    /**
     * @param string $tradeNo
     * @return Response
     * @throws NotFoundException
     */
    #[Validator([
        [Common::class, "clientId"]
    ], Method::COOKIE)]
    public function search(string $tradeNo): Response
    {
        $order = $this->order->getOrder($this->getUser(), $this->getClientId(), $tradeNo);
        return $this->theme(Theme::SEARCH, "Index/Search.html", "查询订单", ["category" => $this->getCategory(), "order" => $order?->toArray()]);
    }

    /**
     * @return Response
     * @throws NotFoundException
     */
    #[Validator([[Common::class, "clientId"]], Method::COOKIE)]
    public function cart(): Response
    {
        $items = $this->cart->getItems($this->getUser(), $this->getClientId());
        $totalAmount = $this->cart->getAmount($this->getUser(), $this->getClientId());
        return $this->theme(Theme::CART, "Index/Cart.html", "购物车", ["category" => $this->getCategory(), "items" => $items, "totalAmount" => $totalAmount]);
    }
}