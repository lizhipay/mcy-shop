<?php
declare(strict_types=1);

namespace App\Controller\User\API;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Group;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Supplier;
use App\Interceptor\User;
use App\Interceptor\Visitor;
use App\Model\Category;
use App\Model\Item;
use App\Model\ItemMarkupTemplate;
use App\Model\ItemSku;
use App\Model\Pay;
use App\Model\PluginConfig;
use App\Model\RepertoryCategory;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemMarkupTemplate;
use App\Model\RepertoryItemSku;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Language\Language;
use Kernel\Plugin\Const\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Decimal;
use Kernel\Util\Str;
use Kernel\Util\Tree;

#[Interceptor(class: [PostDecrypt::class, Visitor::class], type: Interceptor::API)]
class Dict extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\Pay $pay;

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function shopCategory(): Response
    {
        $get = new Get(Category::class);
        $get->setColumn("id", "name", "pid");
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->where("user_id", $this->getUser()->id);
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
     */
    #[Interceptor(class: [User::class, Group::class])]
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
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function getTheme(): Response
    {
        $list = \Kernel\Plugin\Plugin::inst()->getStartups(Plugin::TYPE_THEME, $this->getUserPath());
        $data = [["id" => "default", "name" => "默认模板"]];
        foreach ($list as $item) {
            $data[] = ["id" => $item->name, "name" => $item->info['name'] . "(" . $item->info['version'] . ")"];
        }
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function itemMarkupTemplate(): Response
    {
        $list = ItemMarkupTemplate::query()->where("user_id", $this->getUser()->id)->get();
        $data = [];
        foreach ($list as $item) {
            $data[] = ["id" => $item->id, "name" => $item->name];
        }
        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function repertoryItemMarkupTemplate(): Response
    {
        $list = RepertoryItemMarkupTemplate::query()->where("user_id", $this->getUser()->id)->get();
        $data = [];
        foreach ($list as $item) {
            $data[] = ["id" => $item->id, "name" => $item->name];
        }
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function customer(): Response
    {
        $keywords = $this->request->get("keywords");
        $user = \App\Model\User::query()->where("pid", $this->getUser()->id);
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
    #[Interceptor(class: [User::class, Merchant::class])]
    public function pay(): Response
    {
        $list = \Kernel\Plugin\Plugin::instance()->getStartups(Plugin::TYPE_PAY, Usr::inst()->userToEnv($this->getUser()->id));
        $data = [];
        foreach ($list as $item) {
            $data[] = ["id" => $item->name, "name" => $item->info['name'] . "(" . $item->info['version'] . ")"];
        }
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function masterPay(): Response
    {
        $data = [];
        $masterPays = $this->pay->getMasterPayList($this->getUser());
        $dict = ["product" => "购物", "recharge" => "充值", "plugin" => "插件", "level" => "等级", "group" => "用户组"];
        foreach ($masterPays as $masterPay) {
            $fee = Str::amountRemoveTrailingZeros((new Decimal($masterPay->fee, 3))->mul(100)->getAmount(3));
            $sc = " - [每笔费率:{$fee}%] - 支持业务:[";
            foreach ($masterPay->scope as $scope) {
                $sc .= $dict[$scope] . "/";
            }
            $sc = trim($sc, "/") . "]";
            $data[] = ["id" => $masterPay->id, "name" => $masterPay->name . $sc];
        }
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function payApi(): Response
    {
        $get = new Get(Pay::class);
        $get->setColumn("id", "name");
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->where("user_id", $this->getUser()->id);
        });
        return $this->json(data: $data);
    }

    /**
     * @param string $plugin
     * @param string $handle
     * @return Response
     * @throws RuntimeException
     */
    public function pluginConfig(string $plugin, string $handle): Response
    {
        $config = PluginConfig::where("plugin", $plugin)->where("handle", $handle)->where("user_id", $this->getUser()->id)->get(["id", "name"])->toArray();
        return $this->json(data: $config);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Supplier::class])]
    public function ship(): Response
    {
        $env = Usr::inst()->userToEnv($this->getUser()->id);
        $list = \Kernel\Plugin\Plugin::instance()->getStartups(Plugin::TYPE_SHIP, $env);
        $data = [];
        foreach ($list as $item) {
            $data[] = ["id" => $item->name, "name" => Language::inst()->output($item->info['name']) . "(" . $item->info['version'] . ")"];
        }
        return $this->json(data: $data);
    }


    /**
     * @param string $plugin
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Supplier::class])]
    public function repertoryPluginItem(string $plugin): Response
    {
        $data = RepertoryItem::query()->where("plugin", $plugin)->where("user_id", $this->getUser()->id)->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Supplier::class])]
    public function repertoryItem(): Response
    {
        $data = RepertoryItem::query()->where("user_id", $this->getUser()->id)->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @param int $itemId
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Supplier::class])]
    public function repertoryItemSku(int $itemId): Response
    {
        $data = RepertoryItemSku::query()->where("repertory_item_id", $itemId)->where("user_id", $this->getUser()->id)->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function level(): Response
    {
        return $this->json(data: \App\Model\UserLevel::query()->where("user_id", $this->getUser()->id)->get(["id", "name"])->toArray());
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class])]
    public function bank(): Response
    {
        return $this->json(data: \App\Model\Bank::query()->where("status", 1)->get(["id", "name"])->toArray());
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function item(): Response
    {
        $data = Item::query()->where("user_id", $this->getUser()->id)->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }

    /**
     * @param int $itemId
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [User::class, Merchant::class])]
    public function itemSku(int $itemId): Response
    {
        $data = ItemSku::query()->where("user_id", $this->getUser()->id)->where("item_id", $itemId)->get(["id", "name"])->toArray();
        return $this->json(data: $data);
    }
}