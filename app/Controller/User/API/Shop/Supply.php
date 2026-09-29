<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Entity\Repertory\Trade;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\RepertoryItem;
use App\Service\Common\Query;
use App\Service\Common\RepertoryItemSku;
use App\Service\Common\RepertoryOrder;
use App\Service\User\Ownership;
use App\Validator\Common;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Str;
use Kernel\Validator\Method;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class Supply extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\Supply $supply;

    #[Inject]
    private RepertoryOrder $order;

    #[Inject]
    private \App\Service\User\Item $item;

    #[Inject]
    private Ownership $ownership;


    #[Inject]
    private RepertoryItemSku $repertoryItemSku;

    /**
     * 货源
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(): Response
    {
        $map = $this->request->post();
        $apiCode = $map['api_code'] ?? "";
        $get = new Get(RepertoryItem::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("sort", "asc");
        $get->setColumn("id", "name", "picture_thumb_url", "repertory_category_id", "sort");
        /**
         * @var LengthAwarePaginatorInterface $data
         */
        $data = $this->query->get($get, function (Builder $builder) use ($apiCode, $map) {
            $builder = $builder->with(["sku" => function (HasMany $builder) {
                $builder->orderBy("sort")->select([
                    "id",
                    "repertory_item_id",
                    "picture_url",
                    "picture_thumb_url",
                    "name",
                    "stock_price",
                    "market_control_status",
                    "market_control_min_price",
                    "market_control_max_price",
                    "private_display"
                ]);
            }, "category" => function (HasOne $hasOne) {
                $hasOne->select(['id', "name", "icon"]);
            }])->where("status", 2)->where("is_review", 0);

            if (strlen($apiCode) == 6) {
                $supply = \App\Model\User::query()->where("api_code", $apiCode)->first();
                $builder = $builder->where("user_id", $supply->id ?? 0)->where("privacy", "!=", 0);
            } elseif (strlen($apiCode) == 5) {
                $builder = $builder->where("api_code", $apiCode)->where("privacy", 1);
            } else {
                $builder = $builder->where("privacy", 2);
                //移除代码，此代码是为了 显示供货商自己的货源
                /*        if (!isset($map["search-name"]) || $map["search-name"] === "") {
                            $builder = $builder->orWhere("user_id", $this->getUser()->id);
                        }*/
            }

            return $builder;
        }, Query::RESULT_TYPE_RAW);

        $arr = $data->toArray();

        foreach ($data->items() as $a => $b) {
            foreach ($b->sku as $c => $d) {
                if (!$this->repertoryItemSku->isDisplay($d, $this->getUser())) {
                    unset($arr['data'][$a]["sku"][$c]);
                    continue;
                }
                $arr['data'][$a]["sku"][$c]["stock_price"] = Str::getAmountStr($this->order->getAmount($this->getUser(), $d, 1));
            }

            if (count($arr['data'][$a]["sku"]) > 0) {
                $arr['data'][$a]["sku"] = array_values($arr['data'][$a]["sku"]);
            } else {
                unset($arr['data'][$a]); //隐藏整个商品
            }
        }

        $arr['data'] = array_values($arr['data']);

        //记录本次列表(已通过公开/对接码校验)可见的货源 id 到会话，作为后续 查看/进货/导入 的授权凭据，
        //从而在不改变既有交互(查看/下单不再重复携带对接码)的前提下，杜绝凭 id 直接访问隐藏货源。
        $this->grantSupplyAccess(array_map(fn($r) => (int)($r['id'] ?? 0), $arr['data']));

        return $this->json(data: ["list" => $arr['data'], "total" => $arr['total']]);
    }

    /**
     * 将可见货源 id 合并进会话授权集合（去重、限量）
     * @param array $ids
     * @return void
     */
    private function grantSupplyAccess(array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return;
        }
        $grant = array_map('intval', (array)$this->session->get("supply_grant"));
        $grant = array_values(array_unique(array_merge($grant, $ids)));
        //限量，避免会话无限增长
        if (count($grant) > 3000) {
            $grant = array_slice($grant, -3000);
        }
        $this->session->set("supply_grant", $grant);
    }

    /**
     * 货源访问门禁：公开(privacy=2) / 本人所有 / 会话已授权 / 直接携带匹配对接码 之一方可访问。
     * 用于 查看详情、进货、导入 三处，防止任意商家凭 id 读取/导入/转卖隐藏货源。
     * @param int $itemId
     * @return void
     * @throws JSONException
     */
    private function assertSupplyAccess(int $itemId): void
    {
        /**
         * @var RepertoryItem $item
         */
        $item = RepertoryItem::query()->find($itemId, ["id", "user_id", "privacy", "api_code", "status"]);
        if (!$item || $item->status != 2) {
            throw new JSONException("商品不可用");
        }
        //公开货源
        if ($item->privacy == 2) {
            return;
        }
        //本人货源
        if ((int)$item->user_id === (int)$this->getUser()->id) {
            return;
        }
        //会话授权集合（此前通过对接码列表获得）
        $grant = array_map('intval', (array)$this->session->get("supply_grant"));
        if (in_array((int)$item->id, $grant, true)) {
            return;
        }
        //直接携带匹配的对接码（5位=货源码/privacy=1；6位=供货商码/privacy!=0）
        $apiCode = trim((string)($this->request->post("api_code") ?: $this->request->get("api_code")));
        if ($apiCode !== "") {
            if (strlen($apiCode) === 5 && $item->privacy == 1 && (string)$item->api_code === $apiCode) {
                return;
            }
            if (strlen($apiCode) === 6 && $item->privacy != 0) {
                $supplier = \App\Model\User::query()->where("api_code", $apiCode)->first();
                if ($supplier && (int)$supplier->id === (int)$item->user_id) {
                    return;
                }
            }
        }
        throw new JSONException("无权访问该货源，请先通过对接码获取授权");
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, "id"]
    ], Method::GET)]
    public function item(): Response
    {
        $itemId = $this->request->get("id", Filter::INTEGER);
        $this->assertSupplyAccess((int)$itemId); //可见性门禁：防止凭 id 直接读取隐藏货源
        return $this->json(data: $this->supply->getItem($this->getUser(), $itemId)->toArray());
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function trade(): Response
    {
        $map = $this->request->post();

        //可见性门禁：按 sku 定位货源，校验访问权限，防止越权进货隐藏货源
        $sku = \App\Model\RepertoryItemSku::query()->find((int)($map['repertory_item_sku_id'] ?? 0), ["id", "repertory_item_id"]);
        if (!$sku) {
            throw new JSONException("商品不存在");
        }
        $this->assertSupplyAccess((int)$sku->repertory_item_id);

        $trade = new Trade($this->getUser()->id, (int)$map['repertory_item_sku_id'], (int)$map['quantity']);
        $trade->setTradeNo(Str::generateTradeNo());
        $trade->setMainTradeNo($trade->tradeNo);
        $trade->setWidget($map);
        $order = $this->order->trade($trade, $this->request->clientIp(), true);
        return $this->json(data: ["contents" => $order->contents]);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Supply::class, ["categoryId", "markupId"]]
    ])]
    public function dock(): Response
    {
        $data = (array)$this->request->post("data");
        $categoryId = (int)$this->request->post("category_id");
        $markupId = (int)$this->request->post("markup_id");
        //归属校验：分类与加价模板必须属于当前商家，杜绝跨租户引用他人分类/模板
        if (!\App\Model\Category::query()->where("user_id", $this->getUser()->id)->where("id", $categoryId)->exists()) {
            throw new \Kernel\Exception\JSONException("分类不存在");
        }
        if (!\App\Model\ItemMarkupTemplate::query()->where("user_id", $this->getUser()->id)->where("id", $markupId)->exists()) {
            throw new \Kernel\Exception\JSONException("同步模版配置不存在");
        }
        //可见性门禁：先逐个校验访问权限，防止越权导入隐藏货源
        foreach ($data as $id) {
            $this->assertSupplyAccess((int)$id);
        }
        foreach ($data as $id) {
            $this->item->loadRepertoryItem($categoryId, (int)$id, $markupId, $this->getUser());
        }
        return $this->json();
    }
}