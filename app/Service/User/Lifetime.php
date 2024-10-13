<?php
declare (strict_types=1);

namespace App\Service\User;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Lifetime::class)]
interface Lifetime
{

    /**
     * @param int $userId
     * @param string $ip
     * @param string $ua
     * @return void
     */
    public function create(int $userId, string $ip, string $ua): void;


    /**
     * @param int $userId
     * @param string $column
     * @param int|float|string $value
     * @return void
     */
    public function update(int $userId, string $column, int|float|string $value): void;


    /**
     * @param int $userId
     * @param string|null $column
     * @return mixed
     */
    public function get(int $userId, ?string $column = null): mixed;


    /**
     * @param int $userId
     * @param string $column
     * @param string $amount
     * @return void
     */
    public function increment(int $userId, string $column, string $amount = "1"): void;
}