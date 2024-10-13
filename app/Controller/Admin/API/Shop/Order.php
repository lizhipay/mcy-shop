<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Shop;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
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

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
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
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");
        $row = [];
        $data = $this->query->get($get, function (Builder $builder) use ($map, &$row) {
            $builder = $builder->where("type", "!=", 0);


            if (isset($map['display_scope'])) {
                if ($map['display_scope'] == 1) {
                    $builder = $builder->whereNull("user_id");
                } elseif ($map['display_scope'] == 2) {
                    if (isset($map['user_id']) && $map['user_id'] > 0) {
                        $builder = $builder->where("user_id", $map['user_id']);
                    } else {
                        $builder = $builder->whereNotNull("user_id");
                    }
                }
            }


            $row['order_count'] = (clone $builder)->count();
            $row['order_amount'] = (clone $builder)->sum("total_amount");

            return $builder->withSum('item as dividend_amount', 'dividend_amount')->with([
                "customer" => function (HasOne $one) {
                    $one->select(["id", "username", "avatar"]);
                },
                "user" => function (HasOne $one) {
                    $one->select(["id", "username", "avatar"]);
                },
                "invite" => function (HasOne $one) {
                    $one->select(["id", "username", "avatar"]);
                }
            ]);
        });
        return $this->json(data: array_merge($data, $row));
    }


    /**
     * @param int $id
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([[Common::class, "id"]], Method::GET)]
    public function items(int $id): Response
    {
        $items = OrderItem::with([
            "item" => function (HasOne $one) {
                $one->select(["id", "name", "picture_thumb_url", "picture_url"]);
            },
            "sku" => function (HasOne $one) {
                $one->select(["id", "name", "picture_thumb_url", "picture_url", "repertory_item_sku_id"]);
            }
        ])->where("order_id", $id)->get();

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
    #[Validator([[Common::class, "id"]])]
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
        [\App\Validator\Admin\Order::class, "orderId"]
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
        ])->find($orderId);

        if ($orderItem->status != 1 && $orderItem->status != 3) {
            throw new JSONException("订单还未发货");
        }

        if (!$orderItem->item && !$orderItem->sku) {
            throw new JSONException("该物品已损坏");
        }

        return $this->response
            ->withHeader("Content-Type", "application/octet-stream")
            ->withHeader("Content-Transfer-Encoding", "binary")
            ->withHeader("Content-Disposition", sprintf('filename=%s(%s)-%s.txt', $orderItem->item->name, $orderItem->sku->name, Date::current()))
            ->raw((string)$orderItem->treasure);
    }
}