<?php
declare (strict_types=1);

namespace App\Controller\User\Pay;

use App\Controller\User\Base;
use App\Interceptor\Merchant;
use App\Interceptor\User;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;


#[Interceptor(class: [User::class, Merchant::class])]
class Pay extends Base
{

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->theme(Theme::USER_PAY, "Pay/Pay.html", "支付接口");
    }


    /**
     * @return Response
     */
    public function order(): Response
    {
        return $this->theme(Theme::USER_PAY_ORDER, "Pay/Order.html", "收款记录");
    }
}