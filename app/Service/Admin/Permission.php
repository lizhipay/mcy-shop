<?php
declare (strict_types=1);

namespace App\Service\Admin;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Admin\Bind\Permission::class)]
interface Permission
{

    public const TYPE_MENU = 0;
    public const TYPE_PAGE = 1;
    public const TYPE_API = 2;
    public const TYPE_BUTTON = 3;


    /**
     * @param string $name
     * @param int $pid
     * @param string $route
     * @param int $type
     * @param string|null $icon
     * @param int $rank
     * @param callable|null $callable
     * @return int
     */
    public function add(string $name, int $pid, string $route, int $type, ?string $icon = null, int $rank = 0, ?callable $callable = null): int;


    /**
     * @param int $permissionId
     * @param int $roleId
     * @return void
     */
    public function authorization(int $permissionId, int $roleId): void;
}