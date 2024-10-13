<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryOrder as Model;
use App\Service\Common\Query;
use App\Service\Common\RepertoryOrder;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Order extends Base
{

    #[Inject]
    private Query $query;

    #[Inject]
    private RepertoryOrder $repertoryOrder;

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
        $get->setOrderBy(...$this->query->getOrderBy($map, "id", "desc"));

        $raw = [];

        $data = $this->query->get($get, function (Builder $builder) use ($map, &$raw) {
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

            if (isset($map['trade_no']) && $map['trade_no']) {
                $builder = $builder->where(function (Builder $builder) use ($map) {
                    $builder
                        ->where("trade_no", $map['trade_no'])
                        ->orWhere("item_trade_no", $map['trade_no'])
                        ->orWhere("main_trade_no", $map['trade_no']);
                });
            }

            $raw['order_count'] = (clone $builder)->count();
            $raw['order_amount'] = (clone $builder)->sum("amount");
            $raw['order_supply_profit'] = (clone $builder)->sum("supply_profit");
            $raw['order_office_profit'] = (clone $builder)->sum("office_profit");

            return $builder->with(["supplier", "customer", "item", "sku", "commission"])->withSum("commission as commission_amount", "amount");
        });


        foreach ($data['list'] as $index => $order) {
            $ship = $this->repertoryOrder->getOrderShip($order['id']);
            $data['list'][$index]['render'] = $ship?->isCustomRender() ?? false;
        }

        return $this->json(data: $data, ext: $raw);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([[Common::class, "id"]])]
    public function detail(): Response
    {
        $id = $this->request->post("id", Filter::INTEGER);
        $order = \App\Model\RepertoryOrder::find($id);
        if (!$order) {
            throw new JSONException("订单不存在");
        }

        $ship = $this->repertoryOrder->getOrderShip($order);

        if (!$ship) {
            throw new JSONException("发货插件出现异常");
        }

        return $this->json(data: ["contents" => $ship->isCustomRender() ? $ship->render() : $order->contents]);
    }


}