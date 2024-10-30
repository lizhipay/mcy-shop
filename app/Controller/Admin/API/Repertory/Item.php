<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryItem as Model;
use App\Model\RepertoryItemSku;
use App\Service\Common\Query;
use App\Service\Common\RepertoryItem;
use App\Service\Common\Ship;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasMany;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Str;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Item extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\Item $item;

    #[Inject]
    private \App\Service\Common\RepertoryItemSku $sku;

    #[Inject]
    private RepertoryItem $repertoryItem;

    #[Inject]
    private Ship $ship;

    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(): Response
    {
        $map = $this->request->post();
        $get = new Get(Model::class);
        $get->setWhere($map);

        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy(...$this->query->getOrderBy($map, "sort", "asc"));
        $raw = [];

        $data = $this->query->get($get, function (Builder $builder) use ($map, &$raw) {
            if (isset($map['display_scope'])) {
                if ($map['display_scope'] == 1) {
                    $builder = $builder->whereNull("user_id");
                } elseif ($map['display_scope'] == 2) {
                    if (isset($map['user_id']) && $map['user_id'] > 0) {
                        $builder = $builder->where("user_id", $map['user_id']);
                    } else {
                        $builder = $builder->whereNotNull("user_id");
                    }
                }
            }

            $raw['under_review_count'] = (clone $builder)->where("status", 0)->count();
            $raw['shelves_not_count'] = (clone $builder)->where("status", 1)->count();
            $raw['shelves_have_count'] = (clone $builder)->where("status", 2)->count();
            $raw['banned_count'] = (clone $builder)->where("status", 3)->count();

            return $builder
                ->with(['supplier', 'sku' => function (HasMany $hasMany) {
                    $hasMany->orderBy("sort", "asc")->select(["id", "name", "repertory_item_id", "picture_thumb_url", "picture_url", "stock_price", "supply_price", "cost"]);
                }])
                ->withSum("order as order_amount", "amount")
                ->withSum("todayOrder as today_amount", "amount")
                ->withSum("yesterdayOrder as yesterday_amount", "amount")
                ->withSum("weekdayOrder as weekday_amount", "amount")
                ->withSum("monthOrder as month_amount", "amount")
                ->withSum("lastMonthOrder as last_month_amount", "amount")
                ->withCount("userItem as user_item_count");
        });

        foreach ($data['list'] as &$dat) {
            $path = Usr::inst()->userToEnv($dat['user_id']);
            $plugin = Plugin::instance()->getPlugin($dat['plugin'], $path);
            if ($plugin) {
                $dat["plugin_name"] = $plugin->info['name'] . "(v{$plugin->info['version']})";
            }

            foreach ($dat['sku'] as &$sku) {
                try {
                    $sku['stock'] = $this->ship->stock($sku['id']);
                } catch (\Throwable $e) {
                    $sku['stock'] = "异常";
                }
            }
        }

        return $this->json(data: array_merge($data, $raw));
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Admin\Item::class, "name"]
    ])]
    public function save(): Response
    {
        $save = new Save(Model::class);
        $map = $this->request->post(flags: Filter::NORMAL);
        $save->enableCreateTime();

        $skuTempId = $map['sku_temp_id'] ?? "";
        $local = $map['local'] ?? null;
        $directSale = $map['direct_sale'] ?? null;
        $directCategoryId = $map['direct_category_id'] ?? 0;

        unset($map['direct_sale'], $map['direct_category_id'], $map['local'], $map['sku_temp_id']);

        if ($directSale == 1 && $directCategoryId == 0) {
            throw new JSONException("请选择直营店的商品分类");
        }

        if (!isset($map['id'])) {
            $count = RepertoryItemSku::query()->where("temp_id", $skuTempId)->count();
            if ($count <= 0) {
                throw new JSONException("根据系统的逻辑，每个商品必须至少添加一个SKU。");
            }
            $map['api_code'] = Str::generateRandStr(5);
        }

        $save->setMap(map: $map, forbidden: ["user_id"]);

        try {
            $origin = isset($map['id']) ? Model::find($map['id']) : null;

            //刷新缓存
            if ($origin && ((isset($map['plugin']) && $origin->plugin != $map['plugin']) || (isset($map['status']) && $origin->status != $map['status']))) {
                $this->sku->syncCacheForItem($origin->id);
            }

            $saved = $this->query->save($save);

            if ($origin && isset($map['markup_mode']) && $map['markup_mode'] == 1 && isset($map['markup']) && is_array($origin->markup) && $this->repertoryItem->checkForceSyncRemoteItemPrice($origin->markup, $map['markup'])) {
                $this->repertoryItem->forceSyncRemoteItemPrice($origin->id);
            }

            if (!isset($map['id'])) {
                RepertoryItemSku::query()->where("temp_id", $skuTempId)->whereNull("user_id")->update([
                    "repertory_item_id" => $saved->id
                ]);
            }

            if ($local == 1) {
                $saved->unique_id = null;
                $saved->version = null;
                $saved->save();
                RepertoryItemSku::query()->where("repertory_item_id", $saved->id)->update([
                    "unique_id" => null,
                    "version" => null
                ]);
            }


            //导入直营店
            if ($directSale == 1 && !isset($map['id'])) {
                $this->item->loadRepertoryItem((int)$directCategoryId, (int)$saved->id, [
                    "sync_amount" => 2,
                    "keep_decimals" => 2,
                    "drift_base_amount" => 1,
                    "drift_model" => 1,
                    "drift_value" => 0,
                    "sync_name" => 1,
                    "sync_introduce" => 1,
                    "sync_picture" => 1,
                    "sync_sku_name" => 1,
                    "sync_sku_picture" => 1
                ], available: true);
            }
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->json(message: "保存成功");
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function del(): Response
    {
        $delete = new Delete(Model::class, (array)$this->request->post("list"));
        $this->query->delete($delete);
        return $this->json(message: "删除成功");
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["status", "list"]]
    ])]
    public function updateStatus(): Response
    {
        $list = (array)$this->request->post("list", Filter::INTEGER);
        $status = $this->request->post("status", Filter::INTEGER);

        if (count($list) == 0) {
            throw new JSONException("操作列表不能为空");
        }

        if ($status) {
            foreach ($list as $item) {
                $this->sku->syncCacheForItem((int)$item);
            }
        }

        Model::query()->whereIn("id", $list)->update(["status" => $status == 1 ? 2 : 1]);
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function transferShop(): Response
    {
        $data = (array)$this->request->post("data");
        $categoryId = (int)$this->request->post("category_id");
        $markupId = (int)$this->request->post("markup_id");
        foreach ($data as $id) {
            $this->item->loadRepertoryItem($categoryId, (int)$id, $markupId);
        }
        return $this->json();
    }
}