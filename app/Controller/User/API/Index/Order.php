<?php
declare (strict_types=1);

namespace App\Controller\User\API\Index;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Visitor;
use App\Interceptor\Waf;
use App\Model\OrderItem;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Language\Language;
use Kernel\Plugin\Const\Plugin as PGC;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Date;
use Kernel\Validator\Method;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, Visitor::class], type: Interceptor::API)]
class Order extends Base
{

    #[Inject]
    protected \App\Service\User\Order $order;

    /**
     * @return Response
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([[Common::class, "clientId"]], Method::COOKIE)]
    public function trade(): Response
    {
        if (($hook = Plugin::instance()->hook(Usr::inst()->getEnv(), Point::CONTROLLER_ORDER_TRADE_BEFORE, PGC::HOOK_TYPE_HTTP, $this->request, $this->response)) instanceof Response) return $hook;
        $items = (array)$this->request->post("items");
        $trade = $this->order->trade(
            items: $items,
            clientId: (string)$this->request->cookie("client_id"),
            createIp: $this->request->clientIp(),
            createUa: $this->request->header("UserAgent"),
            customer: $this->getUser(),
            user: $this->getSiteOwner(),
            invite: $this->getInviter()
        );
        if (($hook = Plugin::instance()->hook(Usr::inst()->getEnv(), Point::CONTROLLER_ORDER_TRADE_AFTER, PGC::HOOK_TYPE_HTTP, $trade, $this->request, $this->response)) instanceof Response) return $hook;
        return $this->json(200, "success", $trade->toArray());
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([[\App\Validator\User\Order::class, "tradeNo"]])]
    public function cancel(): Response
    {
        $tradeNo = $this->request->post("trade_no");
        $this->order->cancel($tradeNo);
        return $this->json();
    }


    /**
     * @throws RuntimeException
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\User\Order::class, ["itemId", "tradeNo"]]
    ], Method::POST)]
    public function getOrder(): Response
    {
        $tradeNo = $this->request->post("trade_no");
        $itemId = $this->request->post("item_id", Filter::INTEGER);

        $order = OrderItem::query()
            ->leftJoin("order", "order_item.order_id", "=", "order.id")
            ->where("order.trade_no", $tradeNo)
            ->find($itemId, "order_item.*");

        if (!$order) {
            throw new JSONException("订单不存在");
        }

        $orderItem = $this->order->getOrderItem($order);

        if (!$orderItem) {
            throw new JSONException("订单不存在");
        }

        return $this->json(200, "success", $orderItem->toArray());
    }


    /**
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\User\Order::class, ["itemId", "tradeNo"]]
    ], Method::GET)]
    public function downloadOrder(int $itemId, string $tradeNo): Response
    {
        /**
         * @var OrderItem $order
         */
        $order = OrderItem::query()
            ->leftJoin("order", "order_item.order_id", "=", "order.id")
            ->where("order.trade_no", $tradeNo)
            ->find($itemId, "order_item.*");

        if (!$order || !in_array($order->status, [1, 3, 4])) {
            throw new JSONException("订单不存在");
        }

        $orderItem = $this->order->getOrderItem($order);

        if (!$orderItem || $orderItem->render) {
            throw new JSONException("订单不存在");
        }

        return $this->response
            ->withHeader("Content-Type", "application/octet-stream")
            ->withHeader("Content-Transfer-Encoding", "binary")
            ->withHeader("Content-Disposition", sprintf('filename=%s(%s)-%s.txt', Language::inst()->output(strip_tags($orderItem->item->name)), Language::inst()->output(strip_tags($orderItem->sku->name)), Date::current()))
            ->raw((string)$orderItem->treasure);
    }
}