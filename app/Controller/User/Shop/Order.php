<?php
declare (strict_types=1);

namespace App\Controller\User\Shop;

use App\Controller\User\Base;
use App\Interceptor\Merchant;
use App\Interceptor\User;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: [User::class, Merchant::class])]
class Order extends Base
{

    public function index(): Response
    {
        return $this->theme(Theme::USER_SHOP_ORDER, "Shop/Order.html", "订单管理");
    }

    /**
     * @return Response
     */
    public function summary(): Response
    {
        return $this->theme(Theme::USER_SHOP_SUMMARY, "Shop/OrderSummary.html", "订单汇总");
    }
}