<?php
declare (strict_types=1);

namespace App\Service\User;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Log::class)]
interface Log
{

    /**
     * @param int $userId
     * @param string $content
     * @return void
     */
    public function create(int $userId, string $content): void;
}