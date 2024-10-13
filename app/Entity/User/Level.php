<?php
declare (strict_types=1);

namespace App\Entity\User;

use App\Model\UserLevel;
use Kernel\Component\ToArray;

class Level
{
    use ToArray;

    public int $id;
    public string $icon;
    public string $name;
    public ?LevelUpgradeRequirements $upgradeRequirements = null;

    public string $upgradePrice = "0.00";

    public string $privilegeIntroduce;
    public ?string $privilegeContent = null;

    public bool $upgradeable = false;


    public function __construct(UserLevel $level)
    {
        $this->id = $level->id;
        $this->icon = $level->icon;
        $this->name = $level->name;
        $this->upgradeRequirements = new LevelUpgradeRequirements($level->upgrade_requirements);
        $this->privilegeIntroduce = $level->privilege_introduce;
    }


    /**
     * @param string $privilegeContent
     */
    public function setPrivilegeContent(string $privilegeContent): void
    {
        $this->privilegeContent = $privilegeContent;
    }


    /**
     * @param string $upgradePrice
     */
    public function setUpgradePrice(string $upgradePrice): void
    {
        $this->upgradePrice = $upgradePrice;
    }

    /**
     * @param bool $upgradeable
     */
    public function setUpgradeable(bool $upgradeable): void
    {
        $this->upgradeable = $upgradeable;
    }
}