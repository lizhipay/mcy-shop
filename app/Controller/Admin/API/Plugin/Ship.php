<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Plugin;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\PluginConfig;
use App\Model\RepertoryItemMarkupTemplate;
use App\Service\Common\RepertoryItem;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Usr;
use Kernel\Util\Str;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Ship extends Base
{

    #[Inject]
    private RepertoryItem $repertoryItem;


    /**
     * @param int $configId
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function items(int $configId): Response
    {
        /**
         * @var PluginConfig $config
         */
        $config = PluginConfig::find($configId);
        if (!$config) {
            throw new JSONException("配置不存在");
        }

        $foreignShip = \Kernel\Plugin\Ship::inst()->getForeignShipHandle($config->plugin, Usr::MAIN, is_array($config->config) ? $config->config : []);

        if (!$foreignShip) {
            throw new JSONException("插件不存在");
        }

        $items = $foreignShip->getItems();

        $id = Str::generateRandStr(16);
        $category = [];
        $data = [];

        foreach ($items as $item) {
            $arr = $item->toArray();
            if (!isset($category[$arr['category']])) {
                $category[$arr['category']] = $id;
                $arr['pid'] = $id;
                $data[] = ["id" => $id, "name" => $arr['category'], "pid" => 0];
                $data[] = $arr;
                $id = Str::generateRandStr(16);
            } else {
                $arr['pid'] = $category[$arr['category']];
                $data[] = $arr;
            }
        }

        return $this->json(data: ["list" => $data]);
    }


    /**
     * @param int $configId
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    public function import(int $configId): Response
    {
        $categoryId = (int)$this->request->post("category_id", Filter::INTEGER);
        $refundMode = (int)$this->request->post("refund_mode");
        $autoReceiptTime = (int)$this->request->post("auto_receipt_time");
        $markupTemplateId = (int)$this->request->post("markup_template_id");
        $imageDownloadLocal = (bool)$this->request->post("image_download_local", Filter::BOOLEAN);
        $checkRepeat = (bool)$this->request->post("check_repeat", Filter::BOOLEAN);
        $item = $this->request->post("item", Filter::NORMAL);

        if (!is_array($item) || empty($item)) {
            throw new JSONException("请选择要导入的商品");
        }

        if (!RepertoryItemMarkupTemplate::query()->where("id", $markupTemplateId)->exists()) {
            throw new JSONException("远程同步模板不存在");
        }

        $this->repertoryItem->import(null, $markupTemplateId, $categoryId, $configId, $refundMode, $autoReceiptTime, $item, $imageDownloadLocal, $checkRepeat);
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function getSyncRemoteItems(): Response
    {
        $syncRemoteItems = $this->repertoryItem->getSyncRemoteItems(true, null);
        return $this->json(data: $syncRemoteItems);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    public function syncRemoteItem(): Response
    {
        $id = $this->request->post("id", Filter::INTEGER);
        $repertoryItem = \App\Model\RepertoryItem::find($id);
        if (!$repertoryItem) {
            throw new JSONException("商品不存在");
        }
        $this->repertoryItem->syncRemoteItem($repertoryItem);
        return $this->json();
    }
}