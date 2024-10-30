<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Shop;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Item as Model;
use App\Service\Common\Query;
use App\Service\Common\Ship;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Item extends Base
{

    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\Item $item;

    #[Inject]
    private Ship $ship;

    /**
     * @return Response
     * @throws RuntimeException
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
        $data = $this->query->get($get, function (Builder $builder) use (&$raw, $map) {
            if (isset($map['user_id']) && $map['user_id'] > 0) {
                $builder = $builder->where("user_id", $map['user_id']);
            } else {
                $builder = $builder->whereNull("user_id");
            }

            $raw['item_count'] = (clone $builder)->count();
            $raw['not_sold_count'] = (clone $builder)->where("status", 0)->count();
            $raw['sold_count'] = (clone $builder)->where("status", 1)->count();

            return $builder
                ->withSum("order as order_amount", "amount")
                ->withSum("todayOrder as today_amount", "amount")
                ->withSum("yesterdayOrder as yesterday_amount", "amount")
                ->withSum("weekdayOrder as weekday_amount", "amount")
                ->withSum("monthOrder as month_amount", "amount")
                ->withSum("lastMonthOrder as last_month_amount", "amount")
                ->with(['sku' => function (HasMany $hasMany) {
                    $hasMany->orderBy("sort", "asc")->select(["id", "name", "item_id", "picture_thumb_url", "price", "private_display", "repertory_item_sku_id"]);
                }, 'category' => function (HasOne $one) {
                    $one->select(["id", "name", "icon"]);
                }, 'user' => function (HasOne $one) {
                    $one->select(['id', "username", "avatar"]);
                }, 'repertoryItem' => function (HasOne $one) {
                    $one->select(['id', "status"]);
                }
                ]);
        });

        foreach ($data['list'] as $index => $item) {
            foreach ($item['sku'] as $key => $sku) {
                try {
                    $data['list'][$index]["sku"][$key]['stock'] = $this->ship->stock($sku['repertory_item_sku_id']);
                } catch (\Throwable $e) {
                    $data['list'][$index]["sku"][$key]['stock'] = "异常";
                }
                unset($data['list'][$index]["sku"][$key]['repertory_item_sku_id']);
            }
        }

        return $this->json(data: array_merge($data, $raw));
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\Item::class, "name"],
        [Common::class, "id"]
    ])]
    public function save(): Response
    {
        $map = $this->request->post(flags: Filter::NORMAL);
        $save = new Save(Model::class);
        $save->disableAddable();
        $save->setMap($map);
        try {
            $this->query->save($save);

            $item = isset($map['id']) ? Model::with([
                "repertoryItem" => function (HasOne $one) {
                    $one->with(["sku" => function (HasMany $hasMany) {
                        $hasMany->with(["wholesale"]);
                    }]);
                }
            ])->find($map['id']) : null;

            $item && $this->item->syncRepertoryItem($item, $item->repertoryItem);
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