<?php
declare (strict_types=1);

namespace App\Service\User;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Group::class)]
interface Group
{
    /**
     * @param int|null $currentGroupId
     * @return array
     */
    public function list(?int $currentGroupId): array;
}