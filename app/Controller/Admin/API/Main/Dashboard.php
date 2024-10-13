<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Main;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\OrderRecharge;
use App\Model\PayOrder;
use App\Model\RepertoryOrder;
use App\Model\User;
use App\Model\UserWithdraw;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Dashboard extends Base
{

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function statistics(): Response
    {
        $date = (int)$this->request->post("date");
        $data = [];

        //今日
        $time = [Date::calcDay(), Date::calcDay(1)];

        switch ($date) {
            //昨日
            case 1:
                $time = [Date::calcDay(-1), Date::calcDay()];
                break;
            //本周
            case 2:
                $time = [Date::getDateByWeekday(1) . " 00:00:00", Date::getDateByWeekday(7) . " 23:59:59"];
                break;
            //本月
            case 3:
                $time = [Date::getFirstDayOfMonth() . " 00:00:00", Date::getLastDayOfMonth() . " 23:59:59"];
                break;
            //上个月
            case 4:
                $time = [Date::getFirstDayOfLastMonth() . " 00:00:00", Date::getLastDayOfLastMonth() . " 23:59:59"];
                break;
            case 5:
                $time = false;
                break;
        }

        $repertoryOrder = RepertoryOrder::query()->where("status", "!=", 3);
        $orderRecharge = OrderRecharge::query()->where("status", 1);
        $payOrder = PayOrder::query()->where("status", 2);
        $user = User::query()->leftJoin("user_lifetime", "user.id", "=", "user_lifetime.user_id");
        $userWithdraw = UserWithdraw::query()->where("status", 1);

        if ($time) {
            $repertoryOrder = $repertoryOrder->whereBetween("trade_time", $time);
            $orderRecharge = $orderRecharge->whereBetween("create_time", $time);
            $payOrder = $payOrder->whereBetween("create_time", $time);
            $registerUser = (clone $user)->whereBetween("user_lifetime.register_time", $time);
            $activeUser = (clone $user)->whereBetween("user_lifetime.last_active_time", $time);
            $userWithdraw = $userWithdraw->whereBetween("handle_time", $time);
        } else {
            $activeUser = $registerUser = (clone $user);
        }

        //全站出货金额
        $data['shipment_amount'] = (clone $repertoryOrder)->sum("amount");
        //全站出货订单
        $data['shipment_count'] = (clone $repertoryOrder)->count();
        //全站出货，平台盈利金额
        $data['shipment_profit'] = (clone $repertoryOrder)->sum("office_profit");
        //充值金额
        $data['recharge_amount'] = (clone $orderRecharge)->sum("amount");
        //充值订单数量
        $data['recharge_count'] = (clone $orderRecharge)->count();
        //全站在线支付金额
        $data['pay_trade_amount'] = (clone $payOrder)->sum("trade_amount");
        //全站余额支付金额
        $data['pay_balance_amount'] = (clone $payOrder)->sum("balance_amount");
        //全站收款订单
        $data['pay_count'] = (clone $payOrder)->count();
        //注册用户数量
        $data['user_register_count'] = (clone $registerUser)->count();
        //新商家
        $data['user_new_merchant_count'] = (clone $registerUser)->whereNotNull("group_id")->count();
        //活跃用户数量
        $data['user_active_count'] = (clone $activeUser)->count();
        //提现金额
        $data['withdraw_amount'] = (clone $userWithdraw)->sum("amount");


        return $this->json(data: $data);
    }
}