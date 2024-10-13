<?php
declare (strict_types=1);

namespace App\Entity\User;

use Kernel\Component\ToArray;

class LevelUpgradeRequirements
{
    use ToArray;

    public string $totalConsumptionAmount;
    public string $totalRechargeAmount;
    public string $totalReferralCount;
    public string $totalProfitAmount;


    /**
     * @param string|array $requirements
     */
    public function __construct(string|array $requirements)
    {
        if (is_string($requirements)) {
            $requirements = json_decode($requirements, true);
        }

        $this->totalConsumptionAmount = $requirements["total_consumption_amount"] ?? "0";
        $this->totalRechargeAmount = $requirements["total_recharge_amount"] ?? "0";
        $this->totalReferralCount = $requirements["total_referral_count"] ?? "0";
        $this->totalProfitAmount = $requirements["total_profit_amount"] ?? "0";
    }
}