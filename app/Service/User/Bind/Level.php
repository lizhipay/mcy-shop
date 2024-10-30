<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Entity\Shop\CreateOrder;
use App\Entity\Shop\Trade;
use App\Model\User;
use App\Model\UserLevel;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Util\Decimal;

class Level implements \App\Service\User\Level
{

    #[Inject]
    private \App\Service\User\Balance $balance;


    /**
     * @param User|null $merchant
     * @return int
     */
    public function getDefaultId(?User $merchant): int
    {
        if ($merchant) {
            return (int)UserLevel::query()->where("user_id", $merchant->id)->orderBy("sort", "asc")->first()?->id;
        }
        return (int)UserLevel::query()->whereNull("user_id")->orderBy("sort", "asc")->first()?->id;
    }

    /**
     * @param User $user
     * @return \App\Entity\User\Level[]
     */
    public function getList(User $user): array
    {
        $query = UserLevel::query()->where("is_upgradable", 1);
        if ($user->pid > 0) {
            $query = $query->where("user_id", $user->pid);
        } else {
            $query = $query->whereNull("user_id");
        }

        /**
         * @var UserLevel $selfLevel
         */
        $selfLevel = UserLevel::query()->find($user->level_id);

        $levels = $query->orderBy("sort", "asc")->get();

        $array = [];

        /**
         * @var UserLevel $level
         */
        foreach ($levels as $level) {
            $entity = new \App\Entity\User\Level($level);
            $entity->setUpgradeable($selfLevel->sort < $level->sort);
            if ($entity->upgradeable) {
                $entity->setUpgradePrice((new Decimal((string)$level->upgrade_price))->sub((string)$selfLevel->upgrade_price)->getAmount());
            }
            $array[] = $entity->toArray();
        }

        return $array;
    }

    /**
     * @param User $user
     * @param int $levelId
     * @param string $clientId
     * @param string $userAgent
     * @param string $clientIp
     * @return Trade
     * @throws JSONException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function trade(User $user, int $levelId, string $clientId, string $userAgent, string $clientIp): Trade
    {
        if ($user->level_id == $levelId) {
            throw new JSONException("你已经是该用户组的成员");
        }

        /**
         * @var UserLevel $level
         */
        $level = UserLevel::with(['user'])->find($levelId);


        if (!$level) {
            throw new JSONException("该用户组不存在");
        }

        if ($level->upgrade_price == 0) {
            throw new JSONException("该用户组无法通过付费升级");
        }


        /**
         * @var \App\Service\User\Order $orderService
         */
        $orderService = Di::inst()->make(\App\Service\User\Order::class);
        $orderService->clearUnpaidOrder($user->id, \App\Const\Order::ORDER_TYPE_UPGRADE_LEVEL);

        /**
         * @var UserLevel $selfLevel
         */
        $selfLevel = UserLevel::query()->find($user->level_id);

        return Db::transaction(function () use ($user, $level, $clientId, $userAgent, $clientIp, $orderService, $selfLevel) {
            $createOrder = new CreateOrder(\App\Const\Order::ORDER_TYPE_UPGRADE_LEVEL, $clientId, $userAgent, $clientIp);
            $createOrder->setAmount((new Decimal((string)$level->upgrade_price))->sub((string)$selfLevel?->upgrade_price)->getAmount());
            $createOrder->setCustomer($user);
            $createOrder->setMerchant($level?->user);
            $createOrder->setOption(["level_id" => $level->id]);
            $createOrder->setProductInfo($level->icon, "升级会员等级({$level->name})");
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
     * @param int $levelId
     * @return bool
     */
    public function upgrade(int $userId, int $levelId): bool
    {
        /**
         * @var User $user
         */
        $user = User::query()->find($userId);
        if (!$user) {
            return false;
        }

        $user->level_id = $levelId;
        return $user->save();
    }
}