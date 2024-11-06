<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Entity\Shop\Markup;
use App\Entity\Shop\QuantityRestriction;
use App\Entity\Shop\Sku;
use App\Entity\Shop\Wholesale;
use App\Model\Item as Model;
use App\Model\ItemMarkupTemplate;
use App\Model\ItemSku;
use App\Model\ItemSkuLevel;
use App\Model\ItemSkuUser;
use App\Model\ItemSkuWholesale;
use App\Model\ItemSkuWholesaleLevel;
use App\Model\ItemSkuWholesaleUser;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryItemSkuWholesale;
use App\Model\User;
use App\Model\UserLevel;
use App\Service\Common\RepertoryOrder;
use App\Service\Common\Ship;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Log\Log;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Date;
use Kernel\Util\Decimal;
use Kernel\Util\Str;

class Item implements \App\Service\User\Item
{

    #[Inject]
    protected RepertoryOrder $order;

    #[Inject]
    protected \App\Service\User\Order $tradeOrder;


    #[Inject]
    protected Ship $ship;


    #[Inject]
    protected \App\Service\Common\RepertoryItemSku $repertoryItemSku;


    /**
     * @param User|null $customer
     * @param int|null $categoryId
     * @param User|null $merchant
     * @param string|null $keywords
     * @param int|null $page
     * @param int|null $size
     * @return array
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function list(?User $customer, ?int $categoryId, ?User $merchant, ?string $keywords = null, ?int $page = null, ?int $size = null): array
    {
        $query = Model::query()
            ->leftJoin("repertory_item", "item.repertory_item_id", "=", "repertory_item.id")
            ->where("item.status", 1)
            ->where("repertory_item.status", 2);

        if ($categoryId) {
            $query = $query->where("item.category_id", $categoryId);
        } else {
            $query = $query->where("item.recommend", 1);
        }
        if ($merchant) {
            $query = $query->where("item.user_id", $merchant->id);
        } else {
            $query = $query->whereNull("item.user_id");
        }

        //模糊搜索
        if ($keywords) {
            $query = $query->where("item.name", "like", "%{$keywords}%");
        }

        $query = $query->orderBy("item.sort", "asc")->with(["category", "sku" => function (Relation $relation) {
            $relation->orderBy("sort", "asc");
        }]);

        if ($page > 0 && $size > 0) {
            $data = $query->paginate($size, ["item.*", "repertory_item.status as repertory_status"], '', $page)->items();
        } else {
            $data = $query->get(["item.*", "repertory_item.status as repertory_status"]);
        }

        $list = [];

        foreach ($data as $d) {
            $item = $this->getItemEntity($customer, $d, $d->sku)?->toArray();
            if ($item) {
                $list[] = $item;
            }
        }

        return $list;
    }


    /**
     * @param User|null $customer
     * @param Model $item
     * @param Collection $itemSku
     * @param bool $source
     * @return \App\Entity\Shop\Item|null
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function getItemEntity(?User $customer, Model $item, Collection $itemSku, bool $source = false): ?\App\Entity\Shop\Item
    {
        $stock = 0;
        $ItemSkuAvailable = [];
        $itemEntity = new \App\Entity\Shop\Item($item);
        $itemEntity->setCategory(new \App\Entity\Shop\Category($item->category->id, $item->category->name, $item->category->icon));
        if ($source) {
            $itemEntity->setSource($item->toArray());
        }

        $itemEntity->setSupplierId($item?->repertoryItem?->user_id);

        foreach ($itemSku as $sku) {
            $skuEntity = new Sku($sku);
            if ($customer) {
                $skuEntity->setPrice(Str::getAmountStr($this->tradeOrder->getAmount($customer, $sku, 1)));
                if ($sku->private_display == 0 || $skuEntity->price != $sku->price) {
                    $skuEntity->setStock($this->ship->stock($sku->repertory_item_sku_id));
                    $skuEntity->setStockAvailable($this->ship->hasEnoughStock($sku->repertory_item_sku_id));
                    $skuEntity->setQuantityRestriction($this->getQuantityRestriction($item->user_id, $sku->repertoryItemSku));
                    $skuEntity->setWholesale($this->getWholesale($customer, $sku->id));
                    $skuEntity->haveWholesale === true && $itemEntity->setHaveWholesale(true);
                    $ItemSkuAvailable[] = $skuEntity;
                    if (is_numeric($skuEntity->stock)) {
                        $stock += $skuEntity->stock;
                    }
                }
            } else {
                if ($sku->private_display == 0) {
                    $skuEntity->setStock($this->ship->stock($sku->repertory_item_sku_id));
                    $skuEntity->setStockAvailable($this->ship->hasEnoughStock($sku->repertory_item_sku_id));
                    $skuEntity->setPrice(Str::getAmountStr($sku->price));
                    $skuEntity->setQuantityRestriction($this->getQuantityRestriction($item->user_id, $sku->repertoryItemSku));
                    $skuEntity->setWholesale($this->getWholesale($customer, $sku->id));
                    $skuEntity->haveWholesale === true && $itemEntity->setHaveWholesale(true);
                    $ItemSkuAvailable[] = $skuEntity;
                    if (is_numeric($skuEntity->stock)) {
                        $stock += $skuEntity->stock;
                    }
                }
            }
        }


        if (count($ItemSkuAvailable) == 0) {
            return null;
        }

        $itemEntity->setStock($stock > 0 ? $stock : $ItemSkuAvailable[0]->stock);
        $itemEntity->setSold($this->tradeOrder->getItemSold($item->repertory_item_id));
        $itemEntity->setSku($ItemSkuAvailable);
        $itemEntity->setAttr((array)json_decode((string)$item->attr, true));
        $itemEntity->setWidget((array)json_decode((string)$item->repertoryItem->widget, true));

        Plugin::inst()->unsafeHook(Usr::inst()->getEnv(), Point::SERVICE_ITEM_GET_ENTITY, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $itemEntity, $customer, $item, $itemSku, $source);
        return $itemEntity;
    }

    /**
     * @param User|null $customer
     * @param int $itemId
     * @param User|null $user
     * @return \App\Entity\Shop\Item
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function getItem(?User $customer, int $itemId, ?User $user): \App\Entity\Shop\Item
    {
        $item = Model::query();

        if ($user) {
            $item = $item->where("user_id", $user->id);
        } else {
            $item = $item->whereNull("user_id");
        }

        /**
         * @var Model $item
         */
        $item = $item->find($itemId);


        if (!$item) {
            throw new JSONException("商品不存在");
        }

        if ($item->status != 1) {
            throw new JSONException("该商品未上架");
        }

        $this->repertoryItemSku->syncCacheForItem($item->repertory_item_id); //同步缓存

        $itemSku = ItemSku::query()->where("item_id", $itemId)->orderBy("sort")->get();

        $itemEntity = $this->getItemEntity($customer, $item, $itemSku, true);

        if (!$itemEntity) {
            throw new JSONException("该商品对你完全隐藏");
        }


        return $itemEntity;
    }

    /**
     * @param int $categoryId
     * @param int $itemId
     * @param int|array $markupId
     * @param User|null $user
     * @param bool $available
     * @return void
     * @throws JSONException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function loadRepertoryItem(int $categoryId, int $itemId, int|array $markupId, ?User $user = null, bool $available = false): void
    {
        if (is_int($markupId) && !ItemMarkupTemplate::query()->where("id", $markupId)->exists()) {
            throw new JSONException("同步模版配置不存在");
        }

        $data = Db::transaction(function () use ($available, $categoryId, $user, $itemId, $markupId) {
            /**
             * @var RepertoryItem $repertoryItem
             */
            $repertoryItem = RepertoryItem::query()->find($itemId);

            if (!$repertoryItem) {
                throw new JSONException("未找到对应的货物(ID:{$itemId})");
            }

            if (is_int($markupId) && $repertoryItem->status != 2) {
                throw new JSONException("「{$repertoryItem->name} 」未上架，无法导入");
            }

            $now = Date::current();


            # add item
            $item = new Model();
            if ($user) {
                $item->user_id = $user->id;
            }

            $item->repertory_item_id = $repertoryItem->id;
            $item->category_id = $categoryId;
            $item->name = $repertoryItem->name;
            $item->introduce = $repertoryItem->introduce;
            $item->picture_url = $repertoryItem->picture_url;
            $item->picture_thumb_url = $repertoryItem->picture_thumb_url;
            $item->attr = $repertoryItem->attr;
            $item->status = $available ? 1 : 0;
            $item->create_time = $now;
            $item->sort = 0;
            $item->markup_mode = is_int($markupId) ? 1 : 0;
            is_int($markupId) && $item->markup_template_id = $markupId;
            is_array($markupId) && $item->markup = $markupId;
            $item->save();

            # add sku
            $repertoryItemSkus = RepertoryItemSku::query()->where("repertory_item_id", $repertoryItem->id)->get();
            /**
             * @var RepertoryItemSku $repertoryItemSku
             */
            foreach ($repertoryItemSkus as $repertoryItemSku) {
                $itemSku = new  ItemSku();
                $itemSku->repertory_item_sku_id = $repertoryItemSku->id;
                $itemSku->item_id = $item->id;
                $itemSku->name = $repertoryItemSku->name;

                if ($item->user_id > 0 && $repertoryItem->user_id > 0 && $item->user_id === $repertoryItem->user_id) {
                    $itemSku->price = $repertoryItemSku->supply_price;
                    $itemSku->stock_price = $repertoryItemSku->supply_price;
                } else {
                    $itemSku->price = ($repertoryItemSku->market_control_status == 1 && $repertoryItemSku->market_control_min_price > 0) ? $repertoryItemSku->market_control_min_price : $repertoryItemSku->stock_price; //市场控制
                    $itemSku->stock_price = $repertoryItemSku->stock_price;
                }

                $itemSku->sort = $repertoryItemSku->sort;
                $itemSku->create_time = $now;
                $itemSku->picture_url = $repertoryItemSku->picture_url;
                $itemSku->picture_thumb_url = $repertoryItemSku->picture_thumb_url;
                if ($user) {
                    $itemSku->user_id = $user->id;
                }
                $itemSku->save();

                # add item_sku_wholesale

                /**
                 * @var RepertoryItemSkuWholesale $repertoryItemSkuWholesale
                 */
                $repertoryItemSkuWholesales = RepertoryItemSkuWholesale::query()->where("sku_id", $repertoryItemSku->id)->get();

                /**
                 * @var RepertoryItemSkuWholesale $repertoryItemSkuWholesale
                 */
                foreach ($repertoryItemSkuWholesales as $repertoryItemSkuWholesale) {
                    $itemSkuWholesale = new ItemSkuWholesale();
                    $itemSkuWholesale->repertory_item_sku_wholesale_id = $repertoryItemSkuWholesale->id;
                    $itemSkuWholesale->sku_id = $itemSku->id;
                    $itemSkuWholesale->quantity = $repertoryItemSkuWholesale->quantity;
                    $itemSkuWholesale->price = $repertoryItemSkuWholesale->stock_price;
                    $itemSkuWholesale->create_time = $now;
                    $itemSkuWholesale->stock_price = $repertoryItemSkuWholesale->stock_price;
                    if ($item->user_id) {
                        $itemSkuWholesale->user_id = $item->user_id;
                    }
                    $itemSkuWholesale->save();
                }
            }


            return [$item, $repertoryItem];
        });


        $this->syncRepertoryItem(...$data);
    }

    /**
     * @param string $amount
     * @param string $percentage
     * @param int $keepDecimals
     * @return string
     */
    public function getPercentageAmount(string $amount, string $percentage, int $keepDecimals): string
    {
        $src = $amount;
        $amount = (new Decimal($amount, 6))->mul($percentage)->add($amount)->getAmount($keepDecimals);
        if ($amount > 0) {
            return $amount;
        }
        return $src;
    }

    /**
     * @param Model $item
     * @param RepertoryItem $repertoryItem
     * @return void
     * @throws \ReflectionException
     */
    public function syncRepertoryItem(Model $item, RepertoryItem $repertoryItem): void
    {
        $now = Date::current();
        $item->attr = $repertoryItem->attr;
        $itemSkus = ItemSku::query()->where("item_id", $item->id)->get();
        /**
         * 检查SKU是否存在，如果不存在就要删除
         * @var ItemSku $itemSku
         */
        foreach ($itemSkus as $itemSku) {
            if (!RepertoryItemSku::query()->where("id", $itemSku->repertory_item_sku_id)->exists()) {
                try {
                    $itemSku->delete();
                } catch (\Throwable $e) {
                    Log::inst()->error($item->name . "->[SKU:{$itemSku->name}] 自动删除失败，请检查该项");
                }
            }
        }

        //获得同步配置
        $markup = $this->getMarkup($item);

        $markup->syncName && $item->name = $repertoryItem->name;
        $markup->syncIntroduce && $item->introduce = $repertoryItem->introduce;
        if ($markup->syncPicture) {
            $item->picture_url = $repertoryItem->picture_url;
            $item->picture_thumb_url = $repertoryItem->picture_thumb_url;
        }

        $item->save();

        $keepDecimals = (int)$markup->keepDecimals;


        /**
         * 检测sku
         * @var RepertoryItemSku $sku
         */
        foreach ($repertoryItem->sku as $sku) {
            $itemSku = ItemSku::query()->where("repertory_item_sku_id", $sku->id)->where("item_id", $item->id)->first();
            //进货价
            $stockPrice = ($item->user_id > 0 && $repertoryItem->user_id > 0 && $item->user_id === $repertoryItem->user_id) ? (string)$sku->supply_price : (string)$sku->stock_price;
            //游客底价
            $touristPrice = $stockPrice;
            //原来的进货价（旧的）
            $oldStockPrice = (string)$itemSku?->stock_price;

            $SKUEntity = $this->repertoryItemSku->getSKUEntity($sku->id, $item?->user_id);

            if ($SKUEntity && $SKUEntity->marketControl) {
                //游客价覆盖
                if ($SKUEntity->marketControlMinPrice > $touristPrice) {
                    $touristPrice = $SKUEntity->marketControlMinPrice;
                }
            }

            if (!$itemSku) {
                //SKU不存在，准备新增
                $itemSku = new  ItemSku();
                $itemSku->repertory_item_sku_id = $sku->id;
                $item->user_id && $itemSku->user_id = $item->user_id;
                $itemSku->item_id = $item->id;
                $itemSku->name = $sku->name;
                $itemSku->sort = $sku->sort;
                $itemSku->create_time = $now;
                $itemSku->picture_url = $sku->picture_url;
                $itemSku->picture_thumb_url = $sku->picture_thumb_url;
                $itemSku->price = $stockPrice;
            } else {
                $markup->syncSkuName && $itemSku->name = $sku->name;
                if ($markup->syncSkuPicture) {
                    $itemSku->picture_url = $sku->picture_url;
                    $itemSku->picture_thumb_url = $sku->picture_thumb_url;
                }
            }

            if ($markup->syncAmount) {
                $itemSku->price = $this->getPercentageAmount($touristPrice, $markup->percentage, $keepDecimals);
            }

            $itemSku->stock_price = $stockPrice;
            $itemSku->save();

            /**
             * 同步会员等级的价格，算法：通过会员等级价格和旧的进货价自动得出站长心里想给会员等定的价格
             * @var array $itemSkuLevels
             */
            $itemSkuLevels = ItemSkuLevel::query()->where("sku_id", $itemSku->id)->get();

            /**
             * 开始同步会员等级价格
             * @var ItemSkuLevel $itemSkuLevel
             */
            foreach ($itemSkuLevels as $itemSkuLevel) {
                if ($oldStockPrice != $stockPrice && $markup->syncAmount) {
                    $itemSkuLevel->price = (new Decimal((string)$itemSkuLevel->price, 6))->div($oldStockPrice)->mul($stockPrice)->getAmount($keepDecimals);
                    $itemSkuLevel->save();
                }
            }

            /**
             * 同步会员密价，算法：同上
             * @var array $itemSkuUsers
             */
            $itemSkuUsers = ItemSkuUser::query()->where("sku_id", $itemSku->id)->get();

            /**
             * 开始同步会员密价
             * @var ItemSkuUser $itemSkuUser
             */
            foreach ($itemSkuUsers as $itemSkuUser) {
                if ($oldStockPrice != $stockPrice && $markup->syncAmount) {
                    $itemSkuUser->price = (new Decimal((string)$itemSkuUser->price, 6))->div($oldStockPrice)->mul($stockPrice)->getAmount($keepDecimals);
                    $itemSkuUser->save();
                }
            }

            $itemSkuWholesales = ItemSkuWholesale::query()->where("sku_id", $itemSku->id)->get();

            /**
             * 检测仓库是否存在批发规则，不存在，则自动删除
             * @var ItemSkuWholesale $itemSkuWholesale
             */
            foreach ($itemSkuWholesales as $itemSkuWholesale) {
                if (!RepertoryItemSkuWholesale::query()->where("id", $itemSkuWholesale->repertory_item_sku_wholesale_id)->exists()) {
                    try {
                        $itemSkuWholesale->delete();
                    } catch (\Throwable $e) {
                        Log::inst()->error($item->name . "->[SKU:{$itemSku->name}]->[批发规则:{$itemSkuWholesale->quantity}] 自动删除失败，请检查该项");
                    }
                }
            }

            /**
             * 同步批发规则价格
             * @var RepertoryItemSkuWholesale $wholesale
             */
            foreach ($sku->wholesale as $wholesale) {
                $itemSkuWholesale = ItemSkuWholesale::query()->where("repertory_item_sku_wholesale_id", $wholesale->id)->where("sku_id", $itemSku->id)->first();
                //进货价
                $stockPrice = (string)$wholesale->stock_price;
                if (!$itemSkuWholesale) {
                    //SKU 批发规则不存在，准备新增
                    $itemSkuWholesale = new ItemSkuWholesale();
                    $itemSkuWholesale->repertory_item_sku_wholesale_id = $wholesale->id;
                    $item->user_id && $itemSkuWholesale->user_id = $item->user_id;;
                    $itemSkuWholesale->sku_id = $itemSku->id;
                    $itemSkuWholesale->stock_price = $wholesale->stock_price;
                    $itemSkuWholesale->create_time = $now;
                    $itemSkuWholesale->price = $stockPrice;
                }

                $itemSkuWholesale->quantity = $wholesale->quantity;
                if ($markup->syncAmount) {
                    $itemSkuWholesale->price = (new Decimal($stockPrice, 6))->mul($markup->percentage)->add($stockPrice)->getAmount($keepDecimals);  //进货价，这里要适当的通过规则进行自动加价
                }
                $itemSkuWholesale->save();
            }
        }
    }

    /**
     * @param int $itemId
     * @return void
     * @throws \ReflectionException
     */
    public function syncRepertoryItems(int $itemId): void
    {
        /**
         * @var RepertoryItem $repertoryItem
         */
        $repertoryItem = RepertoryItem::with([
            "sku" => function (HasMany $hasMany) {
                $hasMany->with(["wholesale"]);
            }
        ])->find($itemId);
        /**
         * @var RepertoryItemSku $repertorySku
         */
        //$repertorySku = RepertoryItemSku::with(['wholesale'])->where("repertory_item_id", $itemId)->get();
        //已对接的货物
        $items = Model::query()->where("repertory_item_id", $itemId)->get();
        /**
         * @var Model $item
         */
        foreach ($items as $item) {
            $this->syncRepertoryItem($item, $repertoryItem);
        }
    }

    /**
     * @param int|Model $item
     * @return Markup
     */
    public function getMarkup(int|Model $item): Markup
    {
        if (is_int($item)) {
            $item = Model::query()->find($item);
        }

        $markup = $item->markup;
        $markupEntity = new Markup();

        if ($item->markup_mode == 1) {
            /**
             * @var ItemMarkupTemplate $template
             */
            $template = ItemMarkupTemplate::query()->find($item->markup_template_id);
            if ($template) {
                $markupEntity->setSyncAmount((bool)$template->sync_amount);
                $markupEntity->setSyncName((bool)$template->sync_name);
                $markupEntity->setSyncIntroduce((bool)$template->sync_introduce);
                $markupEntity->setSyncPicture((bool)$template->sync_picture);
                $markupEntity->setSyncSkuName((bool)$template->sync_sku_name);
                $markupEntity->setSyncSkuPicture((bool)$template->sync_sku_picture);
                $markupEntity->setKeepDecimals((string)$template->keep_decimals);

                if ($template->sync_amount != 1) {
                    $markupEntity->setPercentage("0");
                    return $markupEntity;
                }

                switch ($template->drift_model) {
                    case 0:
                        $markupEntity->setPercentage((string)$template->drift_value);
                        return $markupEntity;
                    case 1:
                        $markupEntity->setPercentage($template->drift_value > 0 ? (new Decimal((string)$template->drift_value, 6))->div($template->drift_base_amount)->getAmount(6) : "0");
                        return $markupEntity;
                    case 2:
                        $markupEntity->setPercentage((string)-$template->drift_value);
                        return $markupEntity;
                    case 3:
                        $markupEntity->setPercentage($template->drift_value > 0 ? (string)-((new Decimal((string)$template->drift_value, 6))->div($template->drift_base_amount)->getAmount(6)) : "0");
                        return $markupEntity;
                }
            }
        } else {
            $markupEntity->setSyncAmount((bool)$markup['sync_amount']);
            $markupEntity->setSyncName((bool)$markup['sync_name']);
            $markupEntity->setSyncIntroduce((bool)$markup['sync_introduce']);
            $markupEntity->setSyncPicture((bool)$markup['sync_picture']);
            $markupEntity->setSyncSkuName((bool)$markup['sync_sku_name']);
            $markupEntity->setSyncSkuPicture((bool)$markup['sync_sku_picture']);
            $markupEntity->setKeepDecimals(isset($markup['keep_decimals']) ? (string)$markup['keep_decimals'] : "2");

            if ($markup['sync_amount'] != 1) {
                $markupEntity->setPercentage("0");
                return $markupEntity;
            }

            switch ($markup['drift_model']) {
                case 0:
                    $markupEntity->setPercentage((string)$markup['drift_value']);
                    return $markupEntity;
                case 1:
                    $markupEntity->setPercentage($markup['drift_value'] > 0 ? (new Decimal((string)$markup['drift_value'], 6))->div($markup['drift_base_amount'])->getAmount(6) : "0");
                    return $markupEntity;
                case 2:
                    $markupEntity->setPercentage((string)-$markup['drift_value']);
                    return $markupEntity;
                case 3:
                    $markupEntity->setPercentage($markup['drift_value'] > 0 ? (string)-((new Decimal((string)$markup['drift_value'], 6))->div($markup['drift_base_amount'])->getAmount(6)) : "0");
                    return $markupEntity;
            }
        }

        return $markupEntity;
    }

    /**
     * @param int $skuId
     * @return ItemSku
     * @throws JSONException
     */
    public function getSku(int $skuId): ItemSku
    {
        $sku = ItemSku::with(["item", "repertoryItemSku"])->find($skuId);

        if (!$sku) {
            throw new JSONException("SKU不存在");
        }

        if ($sku->item->status != 1) {
            throw new JSONException("该商品未上架");
        }

        return $sku;
    }

    /**
     * @param int $markupTemplateId
     * @return void
     * @throws \ReflectionException
     */
    public function syncRepertoryItemForMarkupTemplate(int $markupTemplateId): void
    {
        if (!ItemMarkupTemplate::query()->where("id", $markupTemplateId)->exists()) {
            return;
        }
        $items = Model::with(['repertoryItem'])->where("markup_mode", 1)->where("markup_template_id", $markupTemplateId)->get();
        foreach ($items as $item) {
            $item?->repertoryItem && $this->syncRepertoryItem($item, $item->repertoryItem);
        }
    }


    /**
     * @param User|null $customer
     * @param int $skuId
     * @return Wholesale[]
     */
    public function getWholesale(?User $customer, int $skuId): array
    {
        $list = ItemSkuWholesale::query()->where("sku_id", $skuId)->orderBy("quantity", "asc")->get();
        $data = [];
        /**
         * @var ItemSkuWholesale $li
         */
        foreach ($list as $li) {
            $wholesale = new Wholesale($li->id, $li->quantity, $li->price);
            if ($customer) {
                /**
                 * @var UserLevel $level
                 */
                $level = $customer?->level;
                //等级批发
                if ($level) {
                    /**
                     * @var ItemSkuWholesaleLevel $levelRule
                     */
                    $levelRule = ItemSkuWholesaleLevel::where("level_id", $level->id)->where("wholesale_id", $wholesale->id)->first();
                    ($levelRule && $levelRule->status == 1 && $levelRule->price < $wholesale->price) && $wholesale->setPrice($levelRule->price);
                }
                /**
                 * @var ItemSkuWholesaleUser $userRule
                 */
                $userRule = ItemSkuWholesaleUser::where("customer_id", $customer->id)->where("wholesale_id", $wholesale->id)->first();
                ($userRule && $userRule->status == 1 && $userRule->price < $wholesale->price) && $wholesale->setPrice($userRule->price);
            }

            $data[] = $wholesale;
        }

        return $data;
    }

    /**
     * @param int|null $userId
     * @param ?RepertoryItemSku $itemSku
     * @return QuantityRestriction
     */
    public function getQuantityRestriction(?int $userId, ?RepertoryItemSku $itemSku): QuantityRestriction
    {
        $sku = $this->repertoryItemSku->getSKUEntity($itemSku, $userId);
        if ($sku->marketControl) {
            return new QuantityRestriction((int)$sku?->marketControlMinNum, (int)$sku?->marketControlMaxNum, (int)$sku?->marketControlOnlyNum);
        }
        return new QuantityRestriction();
    }
}