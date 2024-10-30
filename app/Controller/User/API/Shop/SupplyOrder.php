<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
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

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class SupplyOrder extends Base
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
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->where("customer_id", $this->getUser()->id)->with(["item", "sku"]);
        });
        foreach ($data['list'] as $index => $order) {
            $ship = $this->repertoryOrder->getOrderShip($order['id']);
            $data['list'][$index]['render'] = $ship?->isCustomRender() ?? false;
            $data['list'][$index]['is_self_operated'] = (($order['user_id'] === $order['customer_id']) && $order['user_id'] > 0 && $order['customer_id'] > 0);
        }
        return $this->json(data: $data);
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
        /**
         * @var Model $order
         */
        $order = Model::query()->where("customer_id", $this->getUser()->id)->find($id);

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