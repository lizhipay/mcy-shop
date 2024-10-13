<?php
declare (strict_types=1);

namespace App\Service\User;


use App\Entity\Pay\MasterPay;
use App\Entity\Shop\Order;
use App\Model\User;
use App\Model\UserGroup;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Pay::class)]
interface Pay
{
    public const OWNER_OFFICIAL = 0; //官方
    public const OWNER_MERCHANT = 1; //商家


    public const BUSINESS = ["product", "recharge", "plugin", "level", "group"];


    /**
     * @param int $equipment 设备：0=通用，1=手机，2=电脑
     * @param string $business
     * @param User|null $user
     * @param string $amount
     * @param array $options
     * @return array
     */
    public function getList(int $equipment, string $business, ?User $user = null, string $amount = "0", array $options = []): array;


    /**
     * @param int|null $id
     * @return \App\Model\Pay|null
     */
    public function findPay(?int $id): ?\App\Model\Pay;


    /**
     * @param int|null $id
     * @return int|null
     */
    public function findPayOwner(?int $id): ?int;


    /**
     * @param int|null $id
     * @return bool
     */
    public function isCustom(?int $id): bool;


    /**
     * @param int|null $id
     * @return bool
     */
    public function isOfficial(?int $id): bool;


    /**
     * @param User $user
     * @return MasterPay[]
     */
    public function getMasterPayList(User $user): array;

    /**
     * @param int $id
     * @param User $user
     * @param UserGroup|null $group
     * @return MasterPay|null
     */
    public function getMasterPay(int $id, User $user, ?UserGroup $group): ?MasterPay;
}