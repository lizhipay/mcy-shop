<?php
declare (strict_types=1);

namespace App\Controller\User\API\Repertory;

use App\Controller\User\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Supplier;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryItemSku as Model;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Supplier::class], type: Interceptor::API)]
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
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->where("user_id", $this->getUser()->id);
        });
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\User\ItemSku::class, ["name", "supplyPrice"]]
    ])]
    public function save(): Response
    {
        $reExaminationFields = ["picture_url", "picture_thumb_url", "name", "supply_price"];

        $save = new Save(Model::class);
        $save->enableCreateTime();
        $map = $this->request->post(flags: Filter::NORMAL);
        unset($map['sku_temp_id']);

        if (!isset($map['id'])) {
            //create new
            $itemId = (int)$map['repertory_item_id'] ?? 0;
            if ($itemId > 0) {
                $item = RepertoryItem::query()->where("user_id", $this->getUser()->id)->find($itemId);
                if (!$item) {
                    throw new JSONException("商品数据丢失，SKU无法添加");
                }
            }

            $itemId > 0 && $save->addForceMap("repertory_item_id", $itemId);
        } else {
            $sku = RepertoryItemSku::where("user_id", $this->getUser()->id)->find($map['id']);
            if (!$sku) {
                throw new JSONException("SKU不存在");
            }

            foreach ($reExaminationFields as $field) {
                if (isset($map[$field])) {
                    if (trim($sku->$field) != trim($map[$field])) {
                        RepertoryItem::query()->where("id", $sku->repertory_item_id)->update(["status" => 0]);
                        break;
                    }
                }
            }
        }

        $save->addForceMap("user_id", $this->getUser()->id);
        $save->setMap(map: $map, forbidden: ["user_id", "stock_price", "create_time", "repertory_item_id"]);


        try {
            $model = $this->query->save($save);
            if (!isset($map['id'])) {
                RepertoryItem::query()->where("id", $model->repertory_item_id)->update(["status" => 0]);
            }
            //删除没用的SKU
            RepertoryItemSku::query()->where("create_time", "<=", Date::calcDay(-1))->where("user_id", $this->getUser()->id)->whereNull("repertory_item_id")->delete();
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
        $delete->setWhere("user_id", $this->getUser()->id);
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }
}