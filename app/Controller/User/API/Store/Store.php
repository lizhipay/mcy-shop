<?php
declare (strict_types=1);

namespace App\Controller\User\API\Store;

use App\Controller\User\Base;
use App\Interceptor\Group;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Validator\Store\Install;
use App\Validator\Store\Purchase;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Group::class], type: Interceptor::API)]
class Store extends Base
{

    #[Inject]
    private \App\Service\Store\Store $store;


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function list(): Response
    {
        $list = $this->store->list($this->request->post(), $this->getStoreAuth());
        if (isset($list['list'])) {
            foreach ($list['list'] as &$item) {
                if (Plugin::inst()->exist($item['key'], $this->getUserPath())) {
                    $item['installed'] = true;
                } else {
                    $item['installed'] = false;
                }
            }
        }
        return $this->json(data: $list);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Install::class, "key"]
    ])]
    public function install(): Response
    {
        $key = $this->request->post("key");
        $this->store->install($key, $this->getUserPath(), $this->getStoreAuth());
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Install::class, "key"]
    ])]
    public function uninstall(): Response
    {
        $key = $this->request->post("key");
        $this->store->uninstall($key, $this->getUserPath());
        return $this->json();
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function getPluginVersions(): Response
    {
        $pluginVersionKeys = Plugin::inst()->getPluginVersionKeys($this->getUserPath());
        $versions = $this->store->getPluginVersions(array_keys($pluginVersionKeys), $this->getStoreAuth());
        foreach ($versions as $key => $val) {
            if (version_compare($val, $pluginVersionKeys[$key], ">")) {
                Plugin::inst()->setSystemConfig($key, $this->getUserPath(), ["update" => 1]);
            }
        }
        return $this->json(data: $versions);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function getPluginVersionList(): Response
    {
        $list = $this->store->getPluginVersionList((string)$this->request->post("key"), $this->getStoreAuth());
        return $this->json(data: $list);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function pluginUpdate(): Response
    {
        $this->store->pluginVersionUpdate((string)$this->request->post("key"), $this->getUserPath(), $this->getStoreAuth());
        return $this->json();
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Purchase::class, ["type", "itemId", "subscription", "payId"]]
    ])]
    public function purchase(): Response
    {
        $type = $this->request->post("type", Filter::INTEGER);
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        $subscription = $this->request->post("subscription", Filter::INTEGER);
        $payId = $this->request->post("pay_id", Filter::INTEGER);
        $balance = $this->request->post("balance", Filter::BOOLEAN);
        $subscriptionId = (int)$this->request->post("subscription_id");
        $purchase = $this->store->purchase($type, $itemId, $subscription, $subscriptionId, $payId, $balance, $this->request->url() . "/user/store", $this->getStoreAuth());
        return $this->json(data: $purchase);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Purchase::class, ["amount", "payId"]]
    ])]
    public function recharge(): Response
    {
        $amount = (string)$this->request->post("amount");
        $payId = $this->request->post("pay_id", Filter::INTEGER);
        $recharge = $this->store->recharge($amount, $payId, $this->request->url() . "/user/store", $this->getStoreAuth());
        return $this->json(data: $recharge);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function powers(): Response
    {
        return $this->json(data: ["list" => $this->store->powers($this->getStoreAuth())]);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Purchase::class, ["itemId"]]
    ])]
    public function powerDetail(): Response
    {
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        $isGroup = $this->request->post("is_group", Filter::BOOLEAN) ?? false;
        return $this->json(data: $this->store->powerDetail($itemId, $isGroup, $this->getStoreAuth()));
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Purchase::class, ["type", "itemId", "subscription"]]
    ])]
    public function powerRenewal(): Response
    {
        $type = $this->request->post("type", Filter::INTEGER);
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        $subscription = $this->request->post("subscription", Filter::INTEGER);
        return $this->json(data: ["status" => $this->store->powerRenewal($type, $itemId, $subscription, $this->getStoreAuth())]);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Purchase::class, ["type", "itemId"]]
    ])]
    public function openPowerAutoRenewal(): Response
    {
        $type = $this->request->post("type", Filter::INTEGER);
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        return $this->json(data: ["status" => $this->store->openPowerAutoRenewal($type, $itemId, $this->getStoreAuth())]);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Purchase::class, ["type", "itemId"]]
    ])]
    public function powerBind(): Response
    {
        $type = $this->request->post("type", Filter::INTEGER);
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        return $this->json(data: ["status" => $this->store->powerBind($type, $itemId, $this->getStoreAuth())]);
    }

}