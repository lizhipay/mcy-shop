<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use App\Entity\Repertory\CreateItem;
use App\Entity\Repertory\CreateSku;
use App\Entity\Repertory\Markup;
use App\Model\PluginConfig;
use App\Model\RepertoryCategory;
use App\Model\RepertoryItemMarkupTemplate;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryItemSkuGroup;
use App\Model\RepertoryItemSkuUser;
use App\Model\RepertoryItemSkuWholesale;
use App\Model\RepertoryItemSkuWholesaleGroup;
use App\Model\RepertoryItemSkuWholesaleUser;
use Hyperf\Database\Model\Collection;
use Kernel\Annotation\Inject;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Date;
use Kernel\Util\Decimal;
use Kernel\Util\Str;

class RepertoryItem implements \App\Service\Common\RepertoryItem
{

    #[Inject]
    private \App\Service\Common\Image $image;

    #[Inject]
    private \App\Service\Common\Config $config;

    #[Inject]
    private \App\Service\Common\RepertoryItemSku $repertoryItemSku;

    /**
     * @param int|null $userId
     * @param int $markupTemplateId
     * @param int $categoryId
     * @param int $configId
     * @param int $refundMode
     * @param int $autoReceiptTime
     * @param array $items
     * @param bool $imageDownloadLocal
     * @return array
     * @throws JSONException
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function import(?int $userId, int $markupTemplateId, int $categoryId, int $configId, int $refundMode, int $autoReceiptTime, array $items, bool $imageDownloadLocal): array
    {
        if (!RepertoryCategory::where("id", $categoryId)->exists()) {
            throw new JSONException("仓库不存在");
        }

        /**
         * @var PluginConfig $config
         */
        $config = PluginConfig::find($configId);
        if (!$config || $config->user_id != $userId) {
            throw new ServiceException("配置不存在");
        }

        $plugin = Plugin::inst()->getPlugin($config->plugin, Usr::inst()->userToEnv($userId));

        if (!$plugin) {
            throw new ServiceException("插件不存在");
        }
        if ($plugin->state['run'] != 1) {
            throw new ServiceException("插件未启动");
        }

        $count = count($items);
        $success = 0;

        foreach ($items as $item) {
            try {
                /*      移除重复货源检测        if (!isset($item['unique_id']) || \App\Model\RepertoryItem::query()->where("unique_id", $item['unique_id'])->exists()) {
                                 $plugin->log("[{$item['name']}]->失败：已经存在此货源：{$item['unique_id']}", true);
                                 continue;
                             }*/

                $itemPictureUrl = $item['picture_url'];
                $itemPictureThumbUrl = $itemPictureUrl;
                if ($imageDownloadLocal) {
                    list($itemPictureUrl, $itemPictureThumbUrl) = $this->image->downloadRemoteImage($itemPictureUrl, true, $userId);
                }

                $skus = [];
                //sku
                foreach ($item['skus'] as $sku) {
                    $skuPictureUrl = $sku['picture_url'];
                    $skuPictureThumbUrl = $skuPictureUrl;
                    if ($imageDownloadLocal) {
                        list($skuPictureUrl, $skuPictureThumbUrl) = $this->image->downloadRemoteImage($skuPictureUrl, true, $userId);
                    }

                    $createSku = new CreateSku((isset($sku['versions']) && is_array($sku['versions'])) ? $sku['versions'] : [], $sku['name'], $skuPictureUrl, $skuPictureThumbUrl, $sku['price']);
                    $sku['market_control_only_num'] > 0 && $createSku->setMarketControlOnlyNum($sku['market_control_only_num']);
                    $sku['market_control_max_num'] > 0 && $createSku->setMarketControlMaxNum($sku['market_control_max_num']);
                    $sku['market_control_min_num'] > 0 && $createSku->setMarketControlMinNum($sku['market_control_min_num']);
                    $createSku->setPluginData($sku['options'] ?: []);
                    $createSku->setUniqueId($sku['unique_id']);
                    isset($sku['message']) && $createSku->setMessage($sku['message']);
                    isset($sku['cost']) && $createSku->setCost($sku['cost']);

                    $skus[] = $createSku;
                }

                $introduce = (string)$item['introduce'];

                if ($imageDownloadLocal) {
                    $introduce = preg_replace_callback('/<img[^>]+src=["\']?([^"\'>]+)["\']?[^>]*>/i', function ($matches) use ($userId) {
                        $originalSrc = $matches[1];
                        if (!preg_match('/^(http:\/\/|https:\/\/)/i', $originalSrc)) {
                            return $matches[0];
                        }
                        //下载
                        $downloadRemoteImage = $this->image->downloadRemoteImage($originalSrc, false, $userId);
                        return str_replace($originalSrc, $downloadRemoteImage[0], $matches[0]);
                    }, $introduce);
                }

                $createItem = new CreateItem($markupTemplateId, (isset($item['versions']) && is_array($item['versions'])) ? $item['versions'] : [], $categoryId, $item['name'], $introduce, $itemPictureUrl, $itemPictureThumbUrl, $config->plugin, $skus);
                $createItem->setWidget($item['widgets']);
                $createItem->setAttr($item['attr']);
                $createItem->setRefundMode($refundMode);
                $createItem->setAutoReceiptTime($autoReceiptTime);
                $createItem->setShipConfigId($configId);
                $createItem->setUniqueId($item['unique_id']);
                $createItem->setUserId($userId);
                $createItem->setPluginData($item['options'] ?: []);

                //导入
                $this->create($createItem);
                $plugin->log("[{$item['name']}]->成功!", true);
                $success++;
            } catch (\Throwable $e) {
                $plugin->log("[{$item['name']}]->" . $e->getMessage(), true);
            }
        }

        return ["total" => $count, "success" => $success];
    }

    /**
     * @param CreateItem $createItem
     * @return \App\Entity\Repertory\RepertoryItem
     * @throws ServiceException
     * @throws \Throwable
     */
    public function create(CreateItem $createItem): \App\Entity\Repertory\RepertoryItem
    {
        if (count($createItem->skus) == 0) {
            throw new ServiceException("最少需要提供1个SKU，否则无法导入");
        }

        /**
         * @var RepertoryItemMarkupTemplate $repertoryItemMarkupTemplate
         */
        $repertoryItemMarkupTemplate = RepertoryItemMarkupTemplate::query()->find($createItem->markupTemplateId);

        if (!$repertoryItemMarkupTemplate) {
            throw new ServiceException("远程同步模板不存在");
        }

        $markup = new Markup($repertoryItemMarkupTemplate);


        return Db::transaction(function () use ($markup, $createItem) {
            $repertoryItem = new \App\Model\RepertoryItem();
            $createItem->userId > 0 && $repertoryItem->user_id = $createItem->userId;
            $repertoryItem->repertory_category_id = $createItem->categoryId;
            $repertoryItem->name = $createItem->name;
            $repertoryItem->introduce = $createItem->introduce;
            $repertoryItem->picture_url = $createItem->pictureUrl;
            $repertoryItem->picture_thumb_url = $createItem->pictureThumbUrl;
            $repertoryItem->status = 0;
            $repertoryItem->create_time = Date::current();
            $repertoryItem->plugin = $createItem->plugin;
            !empty($createItem->widget) && $repertoryItem->widget = json_encode($createItem->widget);
            !empty($createItem->attr) && $repertoryItem->attr = json_encode($createItem->attr);
            $repertoryItem->api_code = Str::generateRandStr(5);
            $repertoryItem->privacy = 0;
            $repertoryItem->refund_mode = $createItem->refundMode;
            $repertoryItem->refund_mode == 2 && $repertoryItem->auto_receipt_time = $createItem->autoReceiptTime;
            $repertoryItem->ship_config_id = $createItem->shipConfigId;
            $repertoryItem->version = $createItem->versions;
            $repertoryItem->markup_template_id = $createItem->markupTemplateId;
            $repertoryItem->markup_mode = 2;
            $repertoryItem->update_time = $repertoryItem->create_time;
            $repertoryItem->plugin_data = json_encode($createItem->pluginData);

            !empty($createItem->uniqueId) && $repertoryItem->unique_id = $createItem->uniqueId;
            $repertoryItem->save();

            $skus = [];
            foreach ($createItem->skus as $sku) {
                $skus[] = new \App\Entity\Repertory\RepertoryItemSku($this->createSku($repertoryItem->user_id, $repertoryItem->id, $sku, $markup));
            }

            $repertoryItemEntity = new \App\Entity\Repertory\RepertoryItem($repertoryItem);
            $repertoryItemEntity->setSkus($skus);
            return $repertoryItemEntity;
        });
    }

    /**
     * @param string $amount
     * @param string $exchangeRate
     * @param int $keepDecimals
     * @param string $percentage
     * @return string
     */
    public function getPercentageAmount(string $amount, string $exchangeRate, int $keepDecimals, string $percentage): string
    {
        $currency = $this->config->getCurrency();
        if ($exchangeRate != 0) {
            $amount = (new Decimal($amount, 6))->div($exchangeRate)->getAmount($keepDecimals);
        }
        if ($currency->code != "rmb") {
            $amount = (new Decimal($amount, 6))->div($currency->rate)->getAmount($keepDecimals);
        }
        return (new Decimal($amount, 6))->mul($percentage)->add($amount)->getAmount($keepDecimals);
    }

    /**
     * @param int|null $userId
     * @param int $itemId
     * @param CreateSku $sku
     * @param Markup $markup
     * @return RepertoryItemSku
     */
    public function createSku(?int $userId, int $itemId, CreateSku $sku, Markup $markup): RepertoryItemSku
    {
        $price = $this->getPercentageAmount($sku->price, $markup->exchangeRate, (int)$markup->keepDecimals, $markup->percentage);
        $repertoryItemSku = new RepertoryItemSku();
        $userId > 0 && $repertoryItemSku->user_id = $userId;
        $repertoryItemSku->repertory_item_id = $itemId;
        $repertoryItemSku->picture_url = $sku->pictureUrl;
        $repertoryItemSku->picture_thumb_url = $sku->pictureThumbUrl;
        $repertoryItemSku->name = $sku->name;
        $userId > 0 ? $repertoryItemSku->supply_price = $price : $repertoryItemSku->stock_price = $price;
        $repertoryItemSku->market_control_status = (int)$sku->marketControlStatus;
        $repertoryItemSku->market_control_min_num = $sku->marketControlMinNum;
        $repertoryItemSku->market_control_max_num = $sku->marketControlMaxNum;
        $repertoryItemSku->market_control_only_num = $sku->marketControlOnlyNum;
        $repertoryItemSku->cost = $this->getPercentageAmount($sku->cost ?? $sku->price, $markup->exchangeRate, (int)$markup->keepDecimals, "0");
        $repertoryItemSku->create_time = Date::current();
        $repertoryItemSku->plugin_data = json_encode($sku->pluginData);
        $repertoryItemSku->version = $sku->versions;
        !empty($sku->uniqueId) && $repertoryItemSku->unique_id = $sku->uniqueId;
        $sku->message && $repertoryItemSku->message = $sku->message;
        $repertoryItemSku->save();
        return $repertoryItemSku;
    }


    /**
     * @param \App\Model\RepertoryItem|int $item
     * @return Markup
     */
    public function getMarkup(\App\Model\RepertoryItem|int $item): Markup
    {

        if (is_int($item)) {
            /**
             * @var \App\Model\RepertoryItem $item
             */
            $item = \App\Model\RepertoryItem::query()->find($item);
        }

        $markup = $item->markup;
        $markupEntity = new Markup();

        if ($item->markup_mode == 2) {
            /**
             * @var RepertoryItemMarkupTemplate $template
             */
            $template = RepertoryItemMarkupTemplate::query()->find($item->markup_template_id);
            if ($template) {
                $markupEntity->setSyncAmount((bool)$template->sync_amount);
                $markupEntity->setSyncName((bool)$template->sync_name);
                $markupEntity->setSyncIntroduce((bool)$template->sync_introduce);
                $markupEntity->setSyncPicture((bool)$template->sync_picture);
                $markupEntity->setSyncSkuName((bool)$template->sync_sku_name);
                $markupEntity->setSyncSkuPicture((bool)$template->sync_sku_picture);
                $markupEntity->setSyncRemoteDownload((bool)$template->sync_remote_download);
                $markupEntity->setExchangeRate((string)$template->exchange_rate);
                $markupEntity->setKeepDecimals((string)$template->keep_decimals);

                if ($template->sync_amount != 1) {
                    $markupEntity->setPercentage("0");
                    return $markupEntity;
                }

                if ($template->drift_model == 0) {
                    $markupEntity->setPercentage((string)$template->drift_value);
                    return $markupEntity;
                }

                if ($template->drift_value > 0) {
                    $decimal = new Decimal((string)$template->drift_value, 6);
                    $markupEntity->setPercentage($decimal->div($template->drift_base_amount)->getAmount(6));
                } else {
                    $markupEntity->setPercentage("0");
                }
            }
        } else {
            $markupEntity->setSyncAmount((bool)$markup['sync_amount']);
            $markupEntity->setSyncName((bool)$markup['sync_name']);
            $markupEntity->setSyncIntroduce((bool)$markup['sync_introduce']);
            $markupEntity->setSyncPicture((bool)$markup['sync_picture']);
            $markupEntity->setSyncSkuName((bool)$markup['sync_sku_name']);
            $markupEntity->setSyncSkuPicture((bool)$markup['sync_sku_picture']);
            $markupEntity->setSyncRemoteDownload((bool)$markup['sync_remote_download']);
            $markupEntity->setExchangeRate((string)$markup['exchange_rate']);
            $markupEntity->setKeepDecimals((string)$markup['keep_decimals']);

            if ($markup['sync_amount'] != 1) {
                $markupEntity->setPercentage("0");
                return $markupEntity;
            }


            if ($markup['drift_model'] == 0) {
                $markupEntity->setPercentage((string)$markup['drift_value']);
                return $markupEntity;

            }

            if ($markup['drift_value'] > 0) {
                $decimal = new Decimal((string)$markup['drift_value'], 6);
                $markupEntity->setPercentage($decimal->div((string)$markup['drift_base_amount'])->getAmount(6));
            } else {
                $markupEntity->setPercentage("0");
            }
        }

        return $markupEntity;
    }


    /**
     * @param \App\Model\RepertoryItem $repertoryItem
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function syncRemoteItem(\App\Model\RepertoryItem $repertoryItem): void
    {

        $plugin = Plugin::inst()->getPlugin($repertoryItem->plugin, Usr::inst()->userToEnv($repertoryItem->user_id));
        if (!$plugin) {
            return;
        }

        /**
         * @var PluginConfig $config
         */
        $config = PluginConfig::find($repertoryItem->ship_config_id);

        //同步模版
        $markup = $this->getMarkup($repertoryItem);
        //调用发货插件远程拉取数据
        $foreignShip = \Kernel\Plugin\Ship::inst()->getForeignShipHandle($repertoryItem->plugin, Usr::inst()->userToEnv($repertoryItem->user_id), is_array($config?->config) ? $config->config : []);

        if (!$foreignShip) {
            $plugin->log("[{$repertoryItem->name}]插件未实现[ForeignShip]", true);
            return;
        }

        $item = $foreignShip->getItem($repertoryItem->unique_id, json_decode((string)$repertoryItem->plugin_data, true) ?: []);

        //如果对方不存在当前商品
        if (!$item) {
            $repertoryItem->exception_total = $repertoryItem->exception_total + 1;

            //异常总数大于 6 次，将商品拉入审核中
            if ($repertoryItem->exception_total > 6) {
                $repertoryItem->status = 0;
                $repertoryItem->exception_total = 0;
            }

            $repertoryItem->save();
            return;
        }

        if (empty($repertoryItem->version) || !is_array($repertoryItem->version)) {
            $plugin->log("[{$repertoryItem->name}]商品同步版本丢失", true);
            return;
        }

        #1 商品名称同步
        if ($markup->syncName && $item->versions['name'] != $repertoryItem->version['name']) {
            $repertoryItem->name = $item->name;
        }

        #2 商品图片同步
        if ($markup->syncPicture && $item->versions['picture_url'] != $repertoryItem->version['picture_url']) {
            $pictureUrl = $item->pictureUrl;
            $pictureThumbUrl = $pictureUrl;

            if ($markup->syncRemoteDownload) {
                try {
                    list($pictureUrl, $pictureThumbUrl) = $this->image->downloadRemoteImage($pictureUrl, true, $repertoryItem->user_id);
                } catch (\Throwable $e) {
                    $plugin->log("[{$repertoryItem->name}]商品图片同步失败：{$e->getMessage()}", true);
                    return;
                }
            }

            $repertoryItem->picture_url = $pictureUrl;
            $repertoryItem->picture_thumb_url = $pictureThumbUrl;
        }


        #3 商品说明同步
        if ($markup->syncIntroduce && $item->versions['introduce'] != $repertoryItem->version['introduce']) {
            $introduce = (string)$item->introduce;

            if ($markup->syncRemoteDownload) {
                //将介绍中的图片进行本地化
                $introduce = preg_replace_callback('/<img[^>]+src=["\']?([^"\'>]+)["\']?[^>]*>/i', function ($matches) use ($repertoryItem) {
                    $originalSrc = $matches[1];
                    if (!preg_match('/^(http:\/\/|https:\/\/)/i', $originalSrc)) {
                        return $matches[0];
                    }
                    $downloadRemoteImage = $this->image->downloadRemoteImage($originalSrc, false, $repertoryItem->user_id);
                    return str_replace($originalSrc, $downloadRemoteImage[0], $matches[0]);
                }, $introduce);
            }

            $repertoryItem->introduce = $introduce;
        }

        $repertoryItem->version = $item->versions;
        $repertoryItem->update_time = Date::current();
        $repertoryItem->exception_total = 0;
        $repertoryItem->save();

        #4 SKU同步
        try {
            $skus = RepertoryItemSku::query()->where("repertory_item_id", $repertoryItem->id)->get();

            /**
             * @var RepertoryItemSku $sk
             */
            foreach ($skus as $sk) {
                foreach ($item->skus as $sku) {
                    if ($sku->uniqueId == $sk->unique_id) {
                        continue 2;
                    }
                }
                $sk->delete();
            }

            foreach ($item->skus as $sku) {
                /**
                 * @var RepertoryItemSku $repertoryItemSku
                 */
                $repertoryItemSku = RepertoryItemSku::query()->where("unique_id", $sku->uniqueId)->where("repertory_item_id", $repertoryItem->id)->first();


                if (!$repertoryItemSku) {
                    $skuPictureUrl = $sku->pictureUrl;
                    $skuPictureThumbUrl = $skuPictureUrl;
                    if ($markup->syncRemoteDownload) {
                        list($skuPictureUrl, $skuPictureThumbUrl) = $this->image->downloadRemoteImage($skuPictureUrl, true, $repertoryItem->user_id);
                    }
                    //创建新的SKU
                    $createSku = new CreateSku($sku->versions, $sku->name, $skuPictureUrl, $skuPictureThumbUrl, $sku->price);
                    $sku->marketControlOnlyNum > 0 && $createSku->setMarketControlOnlyNum($sku->marketControlOnlyNum);
                    $sku->marketControlMaxNum > 0 && $createSku->setMarketControlMaxNum($sku->marketControlMaxNum);
                    $sku->marketControlMinNum > 0 && $createSku->setMarketControlMinNum($sku->marketControlMinNum);
                    $createSku->setPluginData($sku->options);
                    $createSku->setUniqueId($sku->uniqueId);
                    $sku->cost && $createSku->setCost($sku->cost);
                    $this->createSku($repertoryItem->user_id, $repertoryItem->id, $createSku, $markup);
                } else {
                    ##1 SKU名称同步
                    if ($markup->syncSkuName && $sku->versions['name'] != $repertoryItemSku->version['name']) {
                        $repertoryItemSku->name = $sku->name;
                    }

                    ##2 SKU图片同步
                    if ($markup->syncSkuPicture && $sku->versions['picture_url'] != $repertoryItemSku->version['picture_url']) {
                        $skuPictureUrl = $sku->pictureUrl;
                        $skuPictureThumbUrl = $skuPictureUrl;
                        if ($markup->syncRemoteDownload) {
                            list($skuPictureUrl, $skuPictureThumbUrl) = $this->image->downloadRemoteImage($skuPictureUrl, true, $repertoryItem->user_id);
                        }

                        $repertoryItemSku->picture_url = $skuPictureUrl;
                        $repertoryItemSku->picture_thumb_url = $skuPictureThumbUrl;
                    }

                    ##3 SKU价格同步
                    if ($markup->syncAmount && $sku->versions['price'] != $repertoryItemSku->version['price']) {

                        $price = $this->getPercentageAmount($sku->price, $markup->exchangeRate, (int)$markup->keepDecimals, $markup->percentage); //新的价格


                        $keepDecimals = (int)$markup->keepDecimals;

                        if ($repertoryItem->user_id > 0) {
                            $originalSupplyPrice = $repertoryItemSku->supply_price;
                            $repertoryItemSku->supply_price = $price;
                            $repertoryItemSku->stock_price = (new Decimal($repertoryItemSku->supply_price, 6))->div($originalSupplyPrice)->mul($repertoryItemSku->stock_price)->getAmount($keepDecimals); //增加进货价格
                            $profitRatio = (new Decimal($repertoryItemSku->supply_price, 6))->div($originalSupplyPrice);
                        } else {
                            $originalStockPrice = $repertoryItemSku->stock_price;
                            $repertoryItemSku->stock_price = $price;
                            $profitRatio = (new Decimal($repertoryItemSku->stock_price, 6))->div($originalStockPrice);
                        }

                        $repertoryItemSku->cost = $this->getPercentageAmount($sku->cost ?? $sku->price, $markup->exchangeRate, (int)$markup->keepDecimals, "0");


                        /**
                         * 同步用户组进货价
                         * @var RepertoryItemSkuGroup[] $repertoryItemSkuGroups
                         */
                        $repertoryItemSkuGroups = RepertoryItemSkuGroup::query()->where("sku_id", $repertoryItemSku->id)->get();
                        foreach ($repertoryItemSkuGroups as $repertoryItemSkuGroup) {
                            $repertoryItemSkuGroup->stock_price = (clone $profitRatio)->mul($repertoryItemSkuGroup->stock_price)->getAmount($keepDecimals);
                            $repertoryItemSkuGroup->save();
                        }

                        /**
                         * 同步商家私密进货价
                         * @var RepertoryItemSkuUser[] $repertoryItemSkuUsers
                         */
                        $repertoryItemSkuUsers = RepertoryItemSkuUser::query()->where("sku_id", $repertoryItemSku->id)->get();
                        foreach ($repertoryItemSkuUsers as $repertoryItemSkuUser) {
                            $repertoryItemSkuUser->stock_price = (clone $profitRatio)->mul($repertoryItemSkuUser->stock_price)->getAmount($keepDecimals);
                            $repertoryItemSkuUser->save();
                        }

                        /**
                         * 同步批发进货价
                         * @var RepertoryItemSkuWholesale[] $repertoryItemSkuWholesales
                         */
                        $repertoryItemSkuWholesales = RepertoryItemSkuWholesale::query()->where("sku_id", $repertoryItemSku->id)->get();
                        foreach ($repertoryItemSkuWholesales as $repertoryItemSkuWholesale) {
                            $repertoryItemSkuWholesale->stock_price = (clone $profitRatio)->mul($repertoryItemSkuWholesale->stock_price)->getAmount($keepDecimals);
                            $repertoryItemSkuWholesale->save();

                            $repertoryItemSkuWholesaleGroups = RepertoryItemSkuWholesaleGroup::query()->where("wholesale_id", $repertoryItemSkuWholesale->id)->get();
                            foreach ($repertoryItemSkuWholesaleGroups as $repertoryItemSkuWholesaleGroup) {
                                $repertoryItemSkuWholesaleGroup->stock_price = (clone $profitRatio)->mul($repertoryItemSkuWholesaleGroup->stock_price)->getAmount($keepDecimals);
                                $repertoryItemSkuWholesaleGroup->save();
                            }

                            $repertoryItemSkuWholesaleUsers = RepertoryItemSkuWholesaleUser::query()->where("wholesale_id", $repertoryItemSkuWholesale->id)->get();
                            foreach ($repertoryItemSkuWholesaleUsers as $repertoryItemSkuWholesaleUser) {
                                $repertoryItemSkuWholesaleUser->stock_price = (clone $profitRatio)->mul($repertoryItemSkuWholesaleUser->stock_price)->getAmount($keepDecimals);
                                $repertoryItemSkuWholesaleUser->save();
                            }
                        }
                    }


                    //同步结束
                    $repertoryItemSku->version = $sku->versions;
                    $repertoryItemSku->save();
                }
            }
        } catch (\Throwable $e) {
            $plugin->log("[{$repertoryItem->name}]SKU同步失败：{$e->getMessage()}", true);
        }

        #5 缓存同步
        $this->repertoryItemSku->syncCacheForItem($repertoryItem->id);
    }


    /**
     * @param \App\Model\RepertoryItem|int $repertoryItem
     * @return void
     */
    public function forceSyncRemoteItemPrice(\App\Model\RepertoryItem|int $repertoryItem): void
    {
        if (is_int($repertoryItem)) {
            $repertoryItem = \App\Model\RepertoryItem::find($repertoryItem);
        }

        if ($repertoryItem->markup_mode == 0) {
            return;
        }

        $list = RepertoryItemSku::query()->where("repertory_item_id", $repertoryItem->id)->get();
        /**
         * @var RepertoryItemSku $sku
         */
        foreach ($list as $sku) {
            if (is_array($sku->version) && isset($sku->version['price'])) {
                $version = $sku->version;
                $version['price'] = "sync";
                $sku->version = $version;
                $sku->save();
            }
        }
        $repertoryItem->update_time = "2007-09-16 00:00:00";
        $repertoryItem->save();
    }

    /**
     * @param bool $isOnlyId
     * @param int|null $userId
     * @param int $second
     * @return array|Collection
     */
    public function getSyncRemoteItems(bool $isOnlyId = true, ?int $userId = null, int $second = 120): array|Collection
    {
        $items = \App\Model\RepertoryItem::query()->where("status", 2)->whereNotNull("unique_id")->where("update_time", "<", \date("Y-m-d H:i:s", time() - $second));
        if ($userId > 0) {
            $items = $items->where("user_id", $userId);
        } else {
            $items = $items->whereNull("user_id");
        }
        if ($isOnlyId) {
            return $items->pluck('id')->toArray();
        }
        return $items->get();
    }

    /**
     * @param array $originMarkup
     * @param array $newMarkup
     * @return bool
     */
    public function checkForceSyncRemoteItemPrice(array $originMarkup, array $newMarkup): bool
    {
        if ($originMarkup["sync_amount"] != $newMarkup["sync_amount"]) {
            return true;
        }

        if ($originMarkup["drift_base_amount"] != $newMarkup["drift_base_amount"]) {
            return true;
        }

        if ($originMarkup["drift_value"] != $newMarkup["drift_value"]) {
            return true;
        }

        if ($originMarkup["drift_model"] != $newMarkup["drift_model"]) {
            return true;
        }

        //兼容店铺同步模版
        if (isset($originMarkup["exchange_rate"]) && $originMarkup["exchange_rate"] != $newMarkup["exchange_rate"]) {
            return true;
        }

        if ($originMarkup["keep_decimals"] != $newMarkup["keep_decimals"]) {
            return true;
        }

        return false;
    }
}