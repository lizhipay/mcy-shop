<?php
declare (strict_types=1);

namespace App\Controller\User\User;

use App\Controller\User\Base;
use App\Interceptor\Identity;
use App\Interceptor\User;
use App\View\Helper;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\ViewException;
use Kernel\Language\Language;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: [User::class, Identity::class])]
class Withdraw extends Base
{

    #[Inject]
    private \App\Service\User\BankCard $bankCard;

    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function index(): Response
    {
        $list = $this->bankCard->list($this->getUser()->id);

        if (count($list) == 0) {
            return $this->response->end()->render(
                template: "302.html",
                data: ["url" => "/user/bank/card", "time" => 1, "message" => Language::instance()->output("请先绑定银行卡")]
            );
        }

        $withdraw = $this->_config->getMainConfig("withdraw");
        $defaultWithdrawAmount = $this->getUser()->withdraw_amount;

        if ($withdraw['max_withdraw_amount'] != 0 && $defaultWithdrawAmount > $withdraw['max_withdraw_amount']) {
            $defaultWithdrawAmount = $withdraw['max_withdraw_amount'];
        }

        if ($withdraw['min_withdraw_amount'] != 0 && $defaultWithdrawAmount < $withdraw['min_withdraw_amount']) {
            $defaultWithdrawAmount = $withdraw['min_withdraw_amount'];
        }


        return $this->theme(Theme::USER_WITHDRAW, "User/Withdraw.html", "提现", [
            'card' => $list,
            'min_withdraw_amount' => $withdraw['min_withdraw_amount'] > 0 ? Helper::inst()->currency() . $withdraw['min_withdraw_amount'] : "无限制",
            'max_withdraw_amount' => $withdraw['max_withdraw_amount'] > 0 ? Helper::inst()->currency() . $withdraw['max_withdraw_amount'] : "无限制",
            "default_withdraw_amount" => $defaultWithdrawAmount
        ]);
    }
}