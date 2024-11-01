<?php
declare (strict_types=1);

namespace App\Controller\User\User;


use App\Controller\User\Base;
use App\Interceptor\User;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;
use Kernel\Util\UserAgent;

#[Interceptor(class: User::class)]
class Recharge extends Base
{
    #[Inject]
    private \App\Service\User\Pay $pay;

    /**
     * @return Response
     */
    public function index(): Response
    {
        $pay = $this->pay->getList(UserAgent::getEquipment($this->request->header("UserAgent")), "recharge");
        return $this->theme(Theme::USER_RECHARGE, "User/Recharge.html", "余额充值", ['pay' => $pay]);
    }
}