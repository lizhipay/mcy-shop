<?php
declare (strict_types=1);

namespace App\Service\User;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\LoginLog::class)]
interface LoginLog
{


    /**
     * @param int $userId
     * @param string $ip
     * @param string $ua
     * @return void
     */
    public function create(int $userId, string $ip, string $ua): void;


    /**
     * 检测2个用户是否同一个人
     * @param int $userId
     * @param int $targetId
     * @return bool
     */
    public function isSame(int $userId, int $targetId): bool;
}