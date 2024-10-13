<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Entity\Pay\MasterPay;
use App\Entity\Shop\Order;
use App\Model\PayGroup;
use App\Model\PayUser;
use App\Model\User;
use App\Model\UserGroup;
use Kernel\Exception\ServiceException;

class Pay implements \App\Service\User\Pay
{
    /**
     * @param int $equipment
     * @param string $business
     * @param User|null $user
     * @param string $amount
     * @param array $options
     * @return array
     * @throws ServiceException
     */
    public function getList(int $equipment, string $business, ?User $user = null, string $amount = "0", array $options = []): array
    {
        if (!in_array($business, \App\Service\User\Pay::BUSINESS)) {
            throw new ServiceException("业务不存在");
        }

        $pay = \App\Model\Pay::query()->where("status", 1)->whereIn("equipment", [0, $equipment]);

        $openUser = false;


        if (in_array($business, ["product", "level"])) {
            $openUser = true;
        }

        if ($user && $openUser) {
            $pay = $pay->where("user_id", $user->id);
            if ($user->balance < $amount) {
                $pay = $pay->where("pid", ">", 0);
            }
        } else {
            $pay = $pay->whereNull("user_id");
        }

        $methods = $pay->orderBy("sort", "asc")->get();

        $items = [];

        $group = $user?->group;

        /**
         * @var \App\Model\Pay $method
         */
        foreach ($methods as $method) {
            if ($method->pid > 0 && $user && !$this->getMasterPay($method->pid, $user, $group)) {
                continue;
            }
            $scope = is_array($method->scope) ? $method->scope : (array)json_decode((string)$method->scope, true);
            if (!in_array($business, $scope)) {
                continue;
            }
            $items[] = (new \App\Entity\Pay\Pay($method))->toArray();
        }
        return $items;
    }

    /**
     * @param int|null $id
     * @return \App\Model\Pay|null
     */
    public function findPay(?int $id): ?\App\Model\Pay
    {
        if ($id <= 0) {
            return null;
        }

        /**
         * @var \App\Model\Pay $pay
         */
        $pay = \App\Model\Pay::query()->find($id);
        if (!$pay) {
            return null;
        }
        return $pay;
    }

    /**
     * @param int|null $id
     * @return bool
     */
    public function isCustom(?int $id): bool
    {
        $payOwner = $this->findPayOwner($id);
        return $payOwner === \App\Service\User\Pay::OWNER_MERCHANT;
    }

    /**
     * @param int|null $id
     * @return bool
     */
    public function isOfficial(?int $id): bool
    {
        $payOwner = $this->findPayOwner($id);
        return $payOwner === \App\Service\User\Pay::OWNER_OFFICIAL;
    }

    /**
     * @param int|null $id
     * @return int|null
     */
    public function findPayOwner(?int $id): ?int
    {
        $pay = $this->findPay($id);
        if (!$pay) {
            return null;
        }

        if ($pay->user_id === null) {
            return \App\Service\User\Pay::OWNER_OFFICIAL;
        }

        if ($pay->user_id > 0) {
            return $pay->pid > 0 ? \App\Service\User\Pay::OWNER_OFFICIAL : \App\Service\User\Pay::OWNER_MERCHANT;
        }
        return null;
    }

    /**
     * @param User $user
     * @return MasterPay[]
     */
    public function getMasterPayList(User $user): array
    {
        $list = \App\Model\Pay::query()->where("status", 1)->whereNull("user_id")->orderBy("sort", "asc")->get()->toArray();
        $pays = [];
        $group = $user?->group;

        foreach ($list as $item) {
            $masterPay = $this->getMasterPay($item['id'], $user, $group);
            if ($masterPay) {
                $pays[] = $masterPay;
            }
        }
        return $pays;
    }


    /**
     * @param int $id
     * @param User $user
     * @param UserGroup|null $group
     * @return MasterPay|null
     */
    public function getMasterPay(int $id, User $user, ?UserGroup $group): ?MasterPay
    {
        /**
         * @var \App\Model\Pay $pay
         */
        $pay = \App\Model\Pay::query()->find($id);
        if (!$pay) {
            return null;
        }

        if ($pay->status != 1) {
            return null;
        }

        $scope = is_array($pay->scope) ? $pay->scope : (array)json_decode((string)$pay->scope, true);

        if (empty($scope)) {
            return null;
        }

        if ($group) {
            /**
             * @var PayGroup $payGroup
             */
            $payGroup = PayGroup::query()->where("group_id", $group->id)->where("pay_id", $id)->first();
            if ($payGroup && $payGroup->status == 1) {
                return new MasterPay($pay->id, $pay->name, $pay->icon, $payGroup->fee, $scope);
            }
        }

        /**
         * @var PayUser $payUser
         */
        $payUser = PayUser::query()->where("user_id", $user->id)->where("pay_id", $id)->first();

        if ($payUser && $payUser->status == 1) {
            return new MasterPay($pay->id, $pay->name, $pay->icon, $payUser->fee, $scope);
        }

        if ($pay->substation_status != 1) {
            return null;
        }

        return new MasterPay($pay->id, $pay->name, $pay->icon, $pay->substation_fee, $scope);
    }

}