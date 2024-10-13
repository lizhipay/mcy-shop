<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use App\Model\User;

class CreateOrder
{
    public string $clientId;
    public string $userAgent;
    public string $clientIp;
    public int $type;

    public string $amount = "0.00";
    public ?User $customer = null;
    public ?User $merchant = null;
    public ?User $invite = null;
    public ?array $option = null;


    /**
     * @param int $type
     * @param string $clientId
     * @param string $userAgent
     * @param string $clientIp
     */
    public function __construct(int $type, string $clientId, string $userAgent, string $clientIp)
    {
        $this->clientId = $clientId;
        $this->userAgent = $userAgent;
        $this->type = $type;
        $this->clientIp = $clientIp;
    }

    /**
     * @param string $amount
     */
    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param User|null $customer
     */
    public function setCustomer(?User $customer): void
    {
        $this->customer = $customer;
    }


    /**
     * @param User|null $merchant
     */
    public function setMerchant(?User $merchant): void
    {
        $this->merchant = $merchant;
    }


    /**
     * @param User|null $invite
     */
    public function setInvite(?User $invite): void
    {
        $this->invite = $invite;
    }

    /**
     * @param string $icon
     * @param string $name
     * @param int $quantity
     * @return void
     */
    public function setProductInfo(string $icon, string $name, int $quantity = 1): void
    {
        $this->setOption([
            "product" => [
                "icon" => $icon,
                "name" => $name,
                "quantity" => $quantity
            ]
        ]);
    }


    /**
     * @param array $option
     */
    public function setOption(array $option): void
    {
        if (is_array($this->option)) {
            $this->option = array_merge($this->option, $option);
            return;
        }

        $this->option = $option;
    }
}