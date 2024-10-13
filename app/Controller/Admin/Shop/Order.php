<?php
declare (strict_types=1);

namespace App\Controller\Admin\Shop;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class Order extends Base
{

    public function index(): Response
    {
        return $this->render("Shop/Order.html", "订单管理");
    }

    /**
     * @return Response
     */
    public function summary(): Response
    {
        return $this->render("Shop/OrderSummary.html", "订单汇总");
    }


    /**
     * @return Response
     */
    public function item(): Response
    {
        return $this->render("Shop/OrderItem.html", "物品订单");
    }
}