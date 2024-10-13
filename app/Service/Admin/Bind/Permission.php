<?php
declare (strict_types=1);

namespace App\Service\Admin\Bind;

use App\Model\RolePermission;

class Permission implements \App\Service\Admin\Permission
{

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
    public function add(string $name, int $pid, string $route, int $type, ?string $icon = null, int $rank = 0, ?callable $callable = null): int
    {
        $permission = \App\Model\Permission::query()->where("route", $route)->first();
        if (!$permission) {
            $permission = new \App\Model\Permission();
            $permission->name = $name;
            $permission->pid = $pid;
            $permission->route = $route;
            $permission->type = $type;
            $icon && ($permission->icon = $icon);
            $permission->rank = $rank;
            $permission->save();
        }

        is_callable($callable) && call_user_func($callable, $permission);
        return $permission->id;
    }


    /**
     * @param int $permissionId
     * @param int $roleId
     * @return void
     */
    public function authorization(int $permissionId, int $roleId): void
    {
        if (RolePermission::query()->where("role_id", $roleId)->where("permission_id", $permissionId)->exists()) {
            return;
        }
        $rolePermission = new RolePermission();
        $rolePermission->role_id = $roleId;
        $rolePermission->permission_id = $permissionId;
        $rolePermission->save();
    }
}