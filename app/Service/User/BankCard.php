<?php
declare (strict_types=1);

namespace App\Service\User;


use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\BankCard::class)]
interface BankCard
{

    /**
     * 添加银行卡
     * @param int $userId
     * @param int $bankId
     * @param string $cardNo
     * @param string|null $cardImage
     * @return void
     */
    public function add(int $userId, int $bankId, string $cardNo, ?string $cardImage = null): void;


    /**
     * 设置银行卡异常
     * @param int $cardId
     * @param int $status
     * @return void
     */
    public function abnormality(int $cardId, int $status = 0): void;

    /**
     * 删除银行卡
     * @param int $cardId
     * @return void
     */
    public function del(int $cardId): void;


    /**
     * @param int $userId
     * @return \App\Entity\User\BankCard[]
     */
    public function list(int $userId): array;
}