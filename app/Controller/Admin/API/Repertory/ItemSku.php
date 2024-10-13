<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryItemSku as Model;
use App\Model\RepertoryItemSkuGroup;
use App\Model\RepertoryItemSkuUser;
use App\Service\Common\Query;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class ItemSku extends Base
{
    #[Inject]
    private Query $query;

    /**
     * @param string $id
     * @param string $type
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(string $id, string $type): Response
    {
        if ($type == "add") {
            $map["equal-temp_id"] = $id;
        } else {
            $map["equal-repertory_item_id"] = $id;
        }
        $get = new Get(Model::class);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("sort", "asc");
        $get->setWhere($map);
        $data = $this->query->get($get);
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\ItemSku::class, ["name", "stockPrice"]]
    ])]
    public function save(): Response
    {
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $map = $this->request->post(flags: Filter::NORMAL);
        $skuTempId = $map['sku_temp_id'] ?? "";
        unset($map['sku_temp_id']);

        if (!isset($map['id'])) {
            //create new
            $itemId = (int)$map['repertory_item_id'] ?? 0;

            if ($itemId > 0) {
                $item = RepertoryItem::query()->find($itemId);
                if (!$item) {
                    throw new JSONException("商品数据丢失，SKU无法添加");
                }
            }
        } else {
            unset($map['repertory_item_id']);
        }

        $save->setMap(map: $map, forbidden: ["user_id"]);

        try {
            $saved = $this->query->save($save);

            if (!isset($map['id'])) {
                RepertoryItemSkuGroup::query()->where("temp_id", $skuTempId)->whereNull("user_id")->update([
                    "sku_id" => $saved->id
                ]);
                RepertoryItemSkuUser::query()->where("temp_id", $skuTempId)->whereNull("user_id")->update([
                    "sku_id" => $saved->id
                ]);
            }

            //删除没用的SKU
            RepertoryItemSku::query()->where("create_time", "<=", Date::calcDay(-1))->whereNull("repertory_item_id")->delete();
            //删除没用的group set
            RepertoryItemSkuGroup::query()->where("create_time", "<=", Date::calcDay(-1))->whereNull("sku_id")->delete();
            //删除没用的user set
            RepertoryItemSkuUser::query()->where("create_time", "<=", Date::calcDay(-1))->whereNull("sku_id")->delete();
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }


    /**
     * @return Response
     */
    public function del(): Response
    {
        $delete = new Delete(Model::class, (array)$this->request->post("list"));
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }
}