<?php
declare (strict_types=1);

namespace App\Controller\User\API\Trade;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\OrderItem;
use App\Service\Common\Query;
use App\Service\User\Ownership;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;
use Kernel\Validator\Method;
use Kernel\Waf\Filter;


#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class Order extends Base
{

    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\Order $order;

    #[Inject]
    private Ownership $ownership;

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(): Response
    {
        $map = $this->request->post();
        $get = new Get(OrderItem::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");
        $get->setColumn("order_item.*", "order.trade_no as main_trade_no", "order.status as pay_status");
        $data = $this->query->get($get, function (Builder $builder) use (&$row, $map) {
            $builder = $builder->with([
                "item" => function (HasOne $one) {
                    $one->select(["id", "name", "picture_thumb_url", "picture_url"]);
                },
                "sku" => function (HasOne $one) {
                    $one->select(["id", "name", "picture_thumb_url", "picture_url"]);
                }
            ])
                ->leftJoin("order", "order_item.order_id", "=", "order.id")
                ->where("order.customer_id", $this->getUser()->id);

            if (isset($map['keywords']) && $map['keywords'] !== "") {
                $keywords = urldecode($map['keywords']);
                if (preg_match("/^\d{24}$/", $keywords)) {
                    $builder = $builder->where("order.trade_no", $keywords);
                } else {
                    $builder = $builder
                        ->leftJoin("item", "order_item.item_id", "=", "item.id")
                        ->where("item.name", "like", "%{$keywords}%");
                }
            }

            return $builder;
        });


        foreach ($data['list'] as $index => $item) {
            $ship = $this->order->getOrderItemShip($item['id']);
            $data['list'][$index]['render'] = $ship?->isCustomRender() ?? false;
        }

        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException|JSONException
     */
    #[Validator([[Common::class, "id"]])]
    public function item(): Response
    {
        $orderId = $this->request->post("id", Filter::INTEGER);
        $this->ownership->throw(
            $this->ownership->orderItem($this->getUser()->id, $orderId)
        );
        $orderItem = $this->order->getOrderItem($orderId);

        if (!$orderItem) {
            throw new JSONException("物品不存在");
        }
        return $this->json(data: $orderItem->toArray());
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([[Common::class, "id"]])]
    public function receipt(): Response
    {
        $orderId = $this->request->post("id", Filter::INTEGER);
        $this->ownership->throw(
            $this->ownership->orderItem($this->getUser()->id, $orderId)
        );
        $this->order->receipt($orderId);
        return $this->json();
    }

    /**
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\User\Order::class, "orderId"]
    ], Method::GET)]
    public function download(string $orderId): Response
    {
        $orderItem = OrderItem::with([
            "item" => function (HasOne $one) {
                $one->select(["id", "name", "picture_thumb_url", "picture_url"]);
            },
            "sku" => function (HasOne $one) {
                $one->select(["id", "name", "picture_thumb_url", "picture_url"]);
            }
        ])
            ->leftJoin("order", "order_item.order_id", "=", "order.id")
            ->where("order.customer_id", $this->getUser()->id)
            ->find($orderId);

        if (!$orderItem || !in_array($orderItem->status, [1, 3, 4])) {
            throw new JSONException("订单不存在");
        }

        if (!$orderItem->item || !$orderItem->sku) {
            throw new JSONException("该物品已损坏");
        }

        return $this->response
            ->withHeader("Content-Type", "application/octet-stream")
            ->withHeader("Content-Transfer-Encoding", "binary")
            ->withHeader("Content-Disposition", sprintf('filename=%s(%s)-%s.txt', $orderItem->item->name, $orderItem->sku->name, Date::current()))
            ->raw((string)$orderItem->treasure);
    }
}