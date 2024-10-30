<?php
declare(strict_types=1);

namespace App\Controller\Admin\API;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Category;
use App\Model\Item;
use App\Model\ItemMarkupTemplate;
use App\Model\ItemSku;
use App\Model\Pay;
use App\Model\Permission;
use App\Model\PluginConfig;
use App\Model\RepertoryCategory;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemMarkupTemplate;
use App\Model\RepertoryItemSku;
use App\Model\Role;
use App\Model\User;
use App\Service\Common\Query;
use App\Service\Store\Store;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Language\Language;
use Kernel\Plugin\Const\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Config;
use Kernel\Util\Ip;
use Kernel\Util\Tree;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Dict extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private Store $store;

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function role(): Response
    {
        $get = new Get(Role::class);
        $get->setColumn("id", "name");
        $data = $this->query->get($get);
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function currency(): Response
    {
        $arr = Config::get("currency");
        $currency = [];
        foreach ($arr as $key => $val) {
            $currency[] = ["id" => $key, "name" => $val["name"] . "({$val["symbol"]})"];
        }
        return $this->json(data: $currency);
    }

    /**
     * @param int $type
     * @return Response
     * @throws RuntimeException
     */
    public function permission(int $type): Response
    {
        $get = new Get(Permission::class);
        $get->setColumn("id", "name", "pid");
        $data = $this->query->get($get, function (Builder $builder) use ($type) {
            if ($type == 1) {
                return $builder->whereIn("type", [0, 1]);
            }
            return $builder;
        });
        $generate = Tree::generate($data);
        return $this->json(data: $generate);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function repertoryCategory(): Response
    {
        $get = new Get(RepertoryCategory::class);
        $get->setColumn("id", "name", "pid");
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->where("status", 1);
        });
        foreach ($data as &$item) {
            $item['name'] = strip_tags($item['name']);
        }
        $tree = Tree::generate($data, "id", "pid", "children");
        return $this->json(data: $tree);
    }

    /**
     * @param string $plugin
     * @return Response
     * @throws RuntimeException
     */
    public function repertoryPluginItem(string $plugin): Response
    {
        $data = RepertoryItem::query()->where("plugin", $plugin)->whereNull("user_id")->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @param int $userId
     * @param string $plugin
     * @return Response
     * @throws RuntimeException
     */
    public function repertoryItem(int $userId, string $plugin = ""): Response
    {
        $data = RepertoryItem::query();
        if ($userId > 0) {
            $data->where("user_id", $userId);
        } else {
            $data->whereNull("user_id");
        }

        if ($plugin) {
            $data = $data->where("plugin", $plugin);
        }

        $data = $data->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @param int $itemId
     * @return Response
     * @throws RuntimeException
     */
    public function repertoryItemSku(int $itemId): Response
    {
        $data = RepertoryItemSku::query()->where("repertory_item_id", $itemId)->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function shopCategory(int $userId): Response
    {
        $get = new Get(Category::class);
        $get->setColumn("id", "name", "pid");
        $data = $this->query->get($get, function (Builder $builder) use ($userId) {
            if ($userId > 0) {
                return $builder->where("user_id", $userId);
            } else {
                return $builder->whereNull("user_id");
            }
        });

        foreach ($data as &$item) {
            $item['name'] = strip_tags($item['name']);
        }

        $tree = Tree::generate($data, "id", "pid", "children");
        return $this->json(data: $tree);
    }


    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function theme(): Response
    {
        $list = \Kernel\Plugin\Plugin::inst()->getStartups(Plugin::TYPE_THEME, Usr::MAIN);
        $data = [["id" => "default", "name" => "默认模板"]];
        foreach ($list as $item) {
            $data[] = ["id" => $item->name, "name" => $item->info['name'] . "(" . $item->info['version'] . ")"];
        }
        return $this->json(data: $data);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function ship(int $userId): Response
    {
        $list = \Kernel\Plugin\Plugin::instance()->getStartups(Plugin::TYPE_SHIP, Usr::inst()->userToEnv($userId));
        $data = [];
        foreach ($list as $item) {
            $data[] = ["id" => $item->name, "name" => Language::inst()->output($item->info['name']) . "(" . $item->info['version'] . ")"];
        }
        return $this->json(data: $data);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function pay(int $userId): Response
    {
        $list = \Kernel\Plugin\Plugin::instance()->getStartups(Plugin::TYPE_PAY, Usr::inst()->userToEnv($userId));
        $data = [];
        foreach ($list as $item) {
            $data[] = ["id" => $item->name, "name" => $item->info['name'] . "(" . $item->info['version'] . ")"];
        }
        return $this->json(data: $data);
    }

    /**
     * @param int $type 0 = 普通会员，1=供货商，2=商家
     * @return Response
     * @throws RuntimeException
     */
    public function user(int $type): Response
    {
        $keywords = $this->request->get("keywords");

        $user = User::query();

        if ($type == 0) {
            //$user = $user->whereNull("group_id");
        } elseif ($type == 1) {
            $user = $user
                ->leftJoin("user_group", "user.group_id", "=", "user_group.id")
                ->where("user_group.is_supplier", 1);
        } elseif ($type == 2) {
            $user = $user
                ->leftJoin("user_group", "user.group_id", "=", "user_group.id")
                ->where("user_group.is_merchant", 1);
        }

        if (preg_match("/^[0-9]*$/", $keywords)) {
            $user = $user->where("user.id", $keywords);
        } else {
            $user = $user->where("user.username", "like", '%' . $keywords . '%');
        }

        return $this->json(data: $user->get(["user.id", "user.username as name"])->toArray());
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function group(): Response
    {
        $data = \App\Model\UserGroup::query()->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @param int $pid
     * @return Response
     * @throws RuntimeException
     */
    public function level(int $pid): Response
    {
        $data = \App\Model\UserLevel::query();
        if ($pid > 0) {
            $data = $data->where("user_id", $pid);
        } else {
            $data = $data->whereNull("user_id");
        }
        $data = $data->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function itemMarkupTemplate(int $userId): Response
    {
        $query = ItemMarkupTemplate::query();
        if ($userId > 0) {
            $query = $query->where("user_id", $userId);
        } else {
            $query = $query->whereNull("user_id");
        }
        $list = $query->get();
        $data = [];
        foreach ($list as $item) {
            $data[] = ["id" => $item->id, "name" => $item->name];
        }
        return $this->json(data: $data);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function repertoryItemMarkupTemplate(int $userId): Response
    {
        $query = RepertoryItemMarkupTemplate::query();
        if ($userId > 0) {
            $query = $query->where("user_id", $userId);
        } else {
            $query = $query->whereNull("user_id");
        }
        $list = $query->get();
        $data = [];
        foreach ($list as $item) {
            $data[] = ["id" => $item->id, "name" => $item->name];
        }
        return $this->json(data: $data);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function payApi(int $userId): Response
    {
        $get = new Get(Pay::class);
        $get->setColumn("id", "name");
        $data = $this->query->get($get, function (Builder $builder) use ($userId) {
            if ($userId > 0) {
                return $builder->where("user_id", $userId);
            } else {
                return $builder->whereNull("user_id");
            }
        });
        return $this->json(data: $data);
    }


    /**
     * @param string $plugin
     * @param string $handle
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function pluginConfig(string $plugin, string $handle, int $userId): Response
    {
        $config = PluginConfig::where("plugin", $plugin)->where("handle", $handle);
        if ($userId > 0) {
            $config->where("user_id", $userId);
        } else {
            $config->whereNull("user_id");
        }
        $config = $config->get(["id", "name"])->toArray();
        return $this->json(data: $config);
    }

    /**
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    public function item(int $userId): Response
    {
        $data = Item::query();
        if ($userId > 0) {
            $data->where("user_id", $userId);
        } else {
            $data->whereNull("user_id");
        }
        $data = $data->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @param int $itemId
     * @return Response
     * @throws RuntimeException
     */
    public function itemSku(int $itemId): Response
    {
        $data = ItemSku::query()->where("item_id", $itemId)->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function getStoreGroup(): Response
    {
        $groups = $this->store->getGroup(1, $this->getStoreAuth());
        return $this->json(data: $groups);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function ipMode(): Response
    {
        $address = [];
        $clientIp = $this->request->clientIp(false);
        $clientIp && $address[] = ["name" => $clientIp . " - 自动获取", "id" => "auto"];
        for ($i = 0; $i < 8; $i++) {
            $key = Ip::IP_PROTOCOL_HEADER[$i];
            $clientIp = $this->request->header($key);
            $clientIp = $clientIp ? trim(explode(',', $clientIp)[0] ?? "") : null;
            $clientIp && $address[] = ["name" => $clientIp . " - " . $key, "id" => $key];
        }
        return $this->json(data: $address);
    }
}