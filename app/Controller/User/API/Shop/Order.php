<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\Order as Model;
use App\Model\OrderItem;
use App\Service\Common\Query;
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

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class Order extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\Order $order;

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
        $get = new Get(\App\Model\OrderItem::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");

        $get->setWhereLeftJoin(\App\Model\Order::class, "id", "order_id", [
            "customer_id" => "customer_id",
            "trade_no" => "trade_no",
            "create_ip" => "create_ip"
        ]);
        $row = [];
        $data = $this->query->get($get, function (Builder $builder) use (&$row, $map) {
            $builder = $builder->where("order_item.user_id", $this->getUser()->id);

            $row['order_count'] = (clone $builder)->count();
            $row['order_amount'] = (clone $builder)->sum("amount");

            return $builder->with([
                "item" => function (HasOne $one) {
                    $one->select(["id", "name", "picture_thumb_url", "picture_url"]);
                },
                "sku" => function (HasOne $one) {
                    $one->select(["id", "name", "picture_thumb_url", "picture_url"]);
                },
                "order" => function (HasOne $one) {
                    $one->with([
                        'customer' => function (HasOne $one) {
                            $one->select(["id", "username", "avatar"]);
                        },
                        'user' => function (HasOne $one) {
                            $one->select(["id", "username", "avatar"]);
                        },
                        'invite' => function (HasOne $one) {
                            $one->select(["id", "username", "avatar"]);
                        },
                        "payOrder" => function (HasOne $one) {
                            $one->with(['pay' => function (HasOne $one) {
                                $one->select(["id", "name", "icon"]);
                            }]);
                        }
                    ]);
                }
            ]);
        });

        foreach ($data['list'] as $index => $item) {
            $ship = $this->order->getOrderItemShip($item['id']);
            $data['list'][$index]['render'] = $ship?->isCustomRender() ?? false;
        }

        return $this->json(data: $data, ext: $row);
    }


    /**
     * @param int $id
     * @return Response
     * @throws RuntimeException
     */
    public function items(int $id): Response
    {
        $items = OrderItem::with([
            "item" => function (HasOne $one) {
                $one->select(["id", "name", "picture_thumb_url", "picture_url"]);
            },
            "sku" => function (HasOne $one) {
                $one->select(["id", "name", "picture_thumb_url", "picture_url", "repertory_item_sku_id"]);
            }
        ])->where("order_id", $id)->where("user_id", $this->getUser()->id)->get();

        $arr = $items->toArray();

        foreach ($items as $index => $item) {
            $ship = $this->order->getOrderItemShip($item);
            $arr[$index]['render'] = $ship?->isCustomRender() ?? false;
        }

        return $this->json(data: ["list" => $arr]);
    }


    /**
     * @return Response
     * @throws RuntimeException|JSONException
     */
    #[Validator([[\App\Validator\Shop\OrderItem::class, "id"]])]
    public function item(): Response
    {
        $orderId = $this->request->post("id", Filter::INTEGER);
        $orderItem = $this->order->getOrderItem($orderId);
        if (!$orderItem) {
            throw new JSONException("物品不存在");
        }
        return $this->json(data: $orderItem->toArray());
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
        ])->where("user_id", $this->getUser()->id)->find($orderId);

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