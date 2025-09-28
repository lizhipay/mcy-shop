<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Store;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\User;
use App\Service\Common\Query;
use App\Validator\Common;
use App\Validator\Store\Install;
use App\Validator\Store\Purchase;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\App;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\UserAgent;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Store extends Base
{

    #[Inject]
    private \App\Service\Store\Store $store;


    #[Inject]
    private Query $query;

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
                if (Plugin::inst()->exist($item['key'], App::$mEnv)) {
                    $item['installed'] = true;
                } else {
                    $item['installed'] = false;
                }
            }
        }
        return $this->json(data: $list);
    }

    /**
     * @param int $gift
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function group(int $gift): Response
    {
        $list = $this->store->getGroup($gift, $this->getStoreAuth());
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
        $this->store->install($key, Usr::MAIN, $this->getStoreAuth());
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
        $this->store->uninstall($key, Usr::MAIN);
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
        $isGift = (int)$this->request->post("is_gift", Filter::INTEGER);
        $giftUsername = $this->request->post("gift_username") ?? "";
        $purchase = $this->store->purchase($type, $itemId, $subscription, $subscriptionId, $payId, $balance, $this->request->url() . "/admin/store", $isGift, $giftUsername, $this->getStoreAuth(), UserAgent::isMobile($this->request->header("UserAgent")) ? 1 : 0);
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
        $recharge = $this->store->recharge($amount, $payId, $this->request->url() . "/admin/store", $this->getStoreAuth(), UserAgent::isMobile($this->request->header("UserAgent")) ? 1 : 0);
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
        $powers = $this->store->powers($this->getStoreAuth());

        foreach ($powers as &$item) {
            if (isset($item['key'])) {
                if (Plugin::inst()->exist($item['key'], App::$mEnv)) {
                    $item['installed'] = true;
                } else {
                    $item['installed'] = false;
                }
            }
        }

        return $this->json(data: ["list" => $powers]);
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
        [Purchase::class, ["itemId"]]
    ])]
    public function openSubFree(): Response
    {
        $itemId = $this->request->post("item_id", Filter::INTEGER);
        return $this->json(data: ["status" => $this->store->openSubFree($itemId, $this->getStoreAuth())]);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function subPowers(): Response
    {
        $map = $this->request->post();
        $get = new Get(User::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");
        $get->setColumn("id", "username", "avatar", "group_id", "pid", "status", "balance", "withdraw_amount");
        $data = $this->query->get($get, function (Builder $builder) use ($map, &$raw) {
            return $builder->whereNotNull("group_id")->with([
                'parent' => function (HasOne $query) {
                    $query->select(['id', 'username', 'avatar']);
                },
                'group'
            ]);
        });


        $users = [];
        foreach ($data['list'] as &$user) {
            $store = Plugin::inst()->getStoreUser(Usr::inst()->userToEnv($user['id']));
            $user['store_id'] = $store?->id;
            if ($store) {
                $users[] = $store->id;
            }
        }

        $powers = $this->store->getSubPowers($users, $this->getStoreAuth());

        foreach ($data['list'] as &$user) {
            if (isset($user['store_id'], $powers[$user['store_id']])) {
                $user['store'] = $powers[$user['store_id']];
            }
        }

        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function setSubPower(): Response
    {
        $userId = $this->request->post("user_id", Filter::INTEGER);
        $expireTime = $this->request->post("expire_time") ?: "";
        $status = $this->request->post("status", Filter::INTEGER) ?? 0;
        $store = Plugin::inst()->getStoreUser(Usr::inst()->userToEnv($userId));
        if (!$store) {
            throw new JSONException("此用户未登录应用商店");
        }
        $this->store->setSubPower($store->id, $expireTime, $status, $this->getStoreAuth());
        return $this->json();
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

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function getPluginVersions(): Response
    {
        $pluginVersionKeys = Plugin::inst()->getPluginVersionKeys(Usr::MAIN);
        $versions = $this->store->getPluginVersions(array_keys($pluginVersionKeys), $this->getStoreAuth());
        foreach ($versions as $key => $val) {
            if (version_compare($val, $pluginVersionKeys[$key], ">")) {
                Plugin::inst()->setSystemConfig($key, Usr::MAIN, ["update" => 1]);
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
        $this->store->pluginVersionUpdate((string)$this->request->post("key"), Usr::MAIN, $this->getStoreAuth());
        return $this->json();
    }
}