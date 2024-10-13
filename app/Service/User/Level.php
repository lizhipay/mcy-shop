<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Entity\Shop\Trade;
use App\Model\User;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Level::class)]
interface Level
{

    /**
     * 获取默认等级id
     * @param User|null $merchant
     * @return int
     */
    public function getDefaultId(?User $merchant): int;


    /**
     * @param User $user
     * @return \App\Entity\User\Level[]
     */
    public function getList(User $user): array;


    /**
     * @param User $user
     * @param int $levelId
     * @param string $clientId
     * @param string $userAgent
     * @param string $clientIp
     * @return Trade
     */
    public function trade(User $user, int $levelId, string $clientId, string $userAgent, string $clientIp): Trade;


    /**
     * @param int $userId
     * @param int $levelId
     * @return mixed
     */
    public function upgrade(int $userId, int $levelId): bool;
}