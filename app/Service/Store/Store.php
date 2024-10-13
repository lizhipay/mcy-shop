<?php
declare (strict_types=1);

namespace App\Service\Store;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Store\Bind\Store::class)]
interface Store
{

    /**
     * @param array $post
     * @param Authentication $authentication
     * @return array
     */
    public function list(array $post, Authentication $authentication): array;


    /**
     * @param int $gift
     * @param Authentication $authentication
     * @return array
     */
    public function getGroup(int $gift, Authentication $authentication): array;


    /**
     * @param string $key
     * @param string $env
     * @param Authentication $authentication
     * @return void
     */
    public function install(string $key, string $env, Authentication $authentication): void;


    /**
     * @param string $key
     * @param string $env
     * @return void
     */
    public function uninstall(string $key, string $env): void;


    /**
     * @param string $key
     * @param Authentication $authentication
     * @return int
     */
    public function getPluginType(string $key, Authentication $authentication): int;


    /**
     * @param int $type
     * @param int $itemId
     * @param int $subscription
     * @param int $subscriptionId
     * @param int $payId
     * @param bool $balance
     * @param string $syncUrl
     * @param int $isGift
     * @param string $giftUsername
     * @param Authentication $authentication
     * @return array
     */
    public function purchase(int $type, int $itemId, int $subscription, int $subscriptionId, int $payId, bool $balance, string $syncUrl, int $isGift, string $giftUsername, Authentication $authentication): array;


    /**
     * @param string $amount
     * @param int $payId
     * @param string $syncUrl
     * @param Authentication $authentication
     * @return array
     */
    public function recharge(string $amount, int $payId, string $syncUrl, Authentication $authentication): array;

    /**
     * @param Authentication $authentication
     * @return array
     */
    public function powers(Authentication $authentication): array;

    /**
     * @param int $type
     * @param int $itemId
     * @param int $subscription
     * @param Authentication $authentication
     * @return bool
     */
    public function powerRenewal(int $type, int $itemId, int $subscription, Authentication $authentication): bool;

    /**
     * @param int $type
     * @param int $itemId
     * @param Authentication $authentication
     * @return bool
     */
    public function powerBind(int $type, int $itemId, Authentication $authentication): bool;

    /**
     * @param int $type
     * @param int $itemId
     * @param Authentication $authentication
     * @return bool
     */
    public function openPowerAutoRenewal(int $type, int $itemId, Authentication $authentication): bool;

    /**
     * @param int $itemId
     * @param bool $isGroup
     * @param Authentication $authentication
     * @return array
     */
    public function powerDetail(int $itemId, bool $isGroup, Authentication $authentication): array;

    /**
     * @param array $plugins
     * @param Authentication $authentication
     * @return array
     */
    public function getPluginVersions(array $plugins, Authentication $authentication): array;


    /**
     * @param string $key
     * @param Authentication $authentication
     * @return array
     */
    public function getPluginVersionList(string $key, Authentication $authentication): array;

    /**
     * @param string $key
     * @param string $env
     * @param Authentication $authentication
     * @return void
     */
    public function pluginVersionUpdate(string $key, string $env, Authentication $authentication): void;
}