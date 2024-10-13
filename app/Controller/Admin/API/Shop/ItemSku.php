<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Shop;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\ItemSku as Model;
use App\Service\Common\Query;
use App\Service\Common\RepertoryItemSku;
use App\Validator\Common;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class ItemSku extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private RepertoryItemSku $repertoryItemSku;

    #[Inject]
    private \App\Service\User\Item $item;

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
        $map["equal-item_id"] = $id;
        $get = new Get(Model::class);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("sort", "asc",);
        $get->setWhere($map);
        $data = $this->query->get($get);

        foreach ($data['list'] as $index => $value) {
            $data['list'][$index]['sku_entity'] = $this->repertoryItemSku->getSKUEntity((int)$value['repertory_item_sku_id'], (int)$value['user_id']);
        }

        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    public function save(): Response
    {
        $map = $this->request->post();
        if (isset($map['dividend_amount']) && isset($map['price'])) {
            if ($map['dividend_amount'] > $map['price']) {
                throw new JSONException("分红的金额不能大于售价");
            }
        }

        $sku = \App\Model\ItemSku::with(["item" => function (HasOne $one) {
            $one->with([
                "repertoryItem" => function (HasOne $one) {
                    $one->with(["sku" => function (HasMany $hasMany) {
                        $hasMany->with(["wholesale"]);
                    }]);
                }
            ]);
        }])->find($this->request->post(key: 'id', flags: Filter::INTEGER));

        if (!$sku) {
            throw new JSONException("SKU不存在");
        }

        $save = new Save(Model::class);
        $save->disableAddable();
        $save->setMap($map, ["name", "picture_thumb_url", "picture_url", "price", "sort", "private_display", "dividend_amount"]);
        try {
            $this->query->save($save);
            try {
                $this->item->syncRepertoryItem($sku->item, $sku->item->repertoryItem);
            } catch (\Throwable $e) {

            }
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }


}