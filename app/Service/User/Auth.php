<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Model\User;
use Kernel\Annotation\Bind;


#[Bind(class: \App\Service\User\Bind\Auth::class)]
interface Auth
{
    /**
     * @param string $type
     * @param array $map
     * @return void
     */
    public function sendEmail(string $type, array $map): void;

    /**
     * @param array $map
     * @param string $clientId
     * @param string $ip
     * @param string $ua
     * @param User|null $merchant
     * @param User|null $inviter
     * @return User
     */
    public function register(array $map, string $clientId, string $ip, string $ua, ?User $merchant = null, ?User $inviter = null): User;

    /**
     * @param array $map
     * @param string $ip
     * @param string $ua
     * @param string $clientId
     * @return string
     */
    public function login(array $map, string $ip, string $ua, string $clientId): string;


    /**
     * 设置登录成功，并且返回JWT
     * @param User $user
     * @return string
     */
    public function setLoginSuccess(User $user): string;


    /**
     * @param array $map
     * @return void
     */
    public function reset(array $map): void;
}