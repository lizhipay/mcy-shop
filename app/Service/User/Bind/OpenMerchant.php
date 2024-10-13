<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Entity\Shop\CreateOrder;
use App\Entity\Shop\Trade;
use App\Model\ItemMarkupTemplate;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\UserLevel;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Util\Date;

class OpenMerchant implements \App\Service\User\OpenMerchant
{

    #[Inject]
    private \App\Service\User\Balance $balance;


    /**
     * @param User $user
     * @param int $groupId
     * @param string $clientId
     * @param string $userAgent
     * @param string $clientIp
     * @return Trade
     * @throws JSONException
     * @throws \Throwable
     */
    public function trade(User $user, int $groupId, string $clientId, string $userAgent, string $clientIp): Trade
    {
        if ($user->group_id == $groupId) {
            throw new JSONException("你已经是该用户组的成员");
        }

        /**
         * @var UserGroup $group
         */
        $group = UserGroup::query()->find($groupId);

        if (!$group) {
            throw new JSONException("该用户组不存在");
        }

        if ($group->price == 0) {
            $this->become($user->id, $groupId, false);
            return (new Trade())->setIsFree(true);
        }

        /**
         * @var \App\Service\User\Order $orderService
         */
        $orderService = Di::inst()->make(\App\Service\User\Order::class);

        $orderService->clearUnpaidOrder($user->id, \App\Const\Order::ORDER_TYPE_UPGRADE_GROUP);

        return Db::transaction(function () use ($user, $group, $clientId, $userAgent, $clientIp, $orderService) {
            $createOrder = new CreateOrder(\App\Const\Order::ORDER_TYPE_UPGRADE_GROUP, $clientId, $userAgent, $clientIp);
            $createOrder->setAmount((string)$group->price);
            $createOrder->setCustomer($user);
            $createOrder->setOption(["group_id" => $group->id]);
            $createOrder->setProductInfo("/assets/common/images/open.merchant.png", "升级商家用户组");
            /**
             * @var \App\Model\Order $order
             */
            $order = $orderService->create($createOrder);

            return new Trade(
                $order->trade_no,
                (string)$order->total_amount,
                $order->create_time
            );
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);

    }


    /**
     * @param int $userId
     * @param int $groupId
     * @param bool $isDividend
     * @param string|null $tradeNo
     * @return bool
     */
    public function become(int $userId, int $groupId, bool $isDividend = false, ?string $tradeNo = null): bool
    {
        /**
         * @var User $user
         */
        $user = User::query()->find($userId);
        if (!$user) {
            return false;
        }

        $userGroup = UserGroup::find($groupId);
        if (!$userGroup) {
            return false;
        }

        /**
         * @var User $parent
         */
        $parent = $user->parent;
        if ($parent && $userGroup->dividend_amount > 0 && $isDividend && $userGroup->price > 0) {
            $this->balance->add(
                userId: $parent->id,
                amount: (string)$userGroup->dividend_amount,
                type: \App\Const\Balance::TYPE_SUB_DIVIDEND,
                isWithdraw: true,
                tradeNo: $tradeNo
            );
        }

        if (!$user->group_id) {
            $this->firstInitialization($userId, $userGroup);
        }

        $user->group_id = $groupId;

        return $user->save();
    }


    /**
     * @param int $userId
     * @param UserGroup $group
     * @return void
     */
    public function firstInitialization(int $userId, UserGroup $group): void
    {
        //添加会员等级
        $userLevel = new  UserLevel();
        $userLevel->user_id = $userId;
        $userLevel->icon = "/assets/user/images/lv1.png";
        $userLevel->name = "普通会员";
        $userLevel->upgrade_requirements = json_encode([]);
        $userLevel->upgrade_price = 0;
        $userLevel->privilege_introduce = "这是默认等级";
        $userLevel->privilege_content = "这是默认等级的权益内容";
        $userLevel->sort = 0;
        $userLevel->create_time = Date::current();
        $userLevel->save();

        if ($group->is_merchant == 1) {
            //添加同步模版
            $itemMarkupTemplate = new ItemMarkupTemplate();
            $itemMarkupTemplate->user_id = $userId;
            $itemMarkupTemplate->name = "默认同步模版(固定金额10+1)";
            $itemMarkupTemplate->drift_model = 1;
            $itemMarkupTemplate->drift_value = 1;
            $itemMarkupTemplate->drift_base_amount = 10;
            $itemMarkupTemplate->sync_amount = 1;
            $itemMarkupTemplate->sync_name = 1;
            $itemMarkupTemplate->sync_introduce = 1;
            $itemMarkupTemplate->sync_picture = 1;
            $itemMarkupTemplate->sync_sku_name = 1;
            $itemMarkupTemplate->sync_sku_picture = 1;
            $itemMarkupTemplate->create_time = Date::current();
            $itemMarkupTemplate->save();

            //添加默认分类
            $category = new \App\Model\Category();
            $category->user_id = $userId;
            $category->icon = "/favicon.ico";
            $category->name = "默认分类";
            $category->create_time = Date::current();
            $category->status = 1;
            $category->save();
        }
    }
}