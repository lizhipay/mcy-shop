<?php
declare (strict_types=1);

namespace App\Controller\User\Trade;

use App\Controller\User\Base;
use App\Interceptor\User;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;


#[Interceptor(class: User::class)]
class Order extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->theme(Theme::USER_TRADE_ORDER, "Trade/Order.html", "已买到的宝贝");
    }
}