<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Model\UserGroup;

class Group implements \App\Service\User\Group
{

    /**
     * @param int|null $currentGroupId
     * @return array
     */
    public function list(?int $currentGroupId): array
    {
        $groups = UserGroup::query()->where("is_upgradable", 1);
        if ($currentGroupId > 0) {
            $groups = $groups->where("id", "!=", $currentGroupId);
        }
        $groups = $groups->get();
        $array = [];
        foreach ($groups as $group) {
            $array[] = new \App\Entity\User\Group($group);
        }
        return $array;
    }
}