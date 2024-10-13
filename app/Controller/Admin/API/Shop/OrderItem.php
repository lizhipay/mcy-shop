<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Shop;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class OrderItem extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\Order $order;


    /**
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
            if (isset($map['display_scope'])) {
                if ($map['display_scope'] == 1) {
                    $builder = $builder->whereNull("order_item.user_id");
                } elseif ($map['display_scope'] == 2) {
                    if (isset($map['user_id']) && $map['user_id'] > 0) {
                        $builder = $builder->where("order_item.user_id", $map['user_id']);
                    } else {
                        $builder = $builder->whereNotNull("order_item.user_id");
                    }
                }
            }

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
}