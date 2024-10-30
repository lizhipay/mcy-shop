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

        return $this->json(data: ["list" => $arr['data'], "total" => $arr['total']]);
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
        return $this->json(data: $this->supply->getItem($this->getUser(), $itemId)->toArray());
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function trade(): Response
    {
        $map = $this->request->post();
        $trade = new Trade($this->getUser()->id, (int)$map['repertory_item_sku_id'], (int)$map['quantity']);
        $trade->setTradeNo(Str::generateTradeNo());
        $trade->setMainTradeNo($trade->tradeNo);
        $trade->setWidget($map);
        $order = $this->order->trade($trade, $this->request->clientIp());
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
        foreach ($data as $id) {
            $this->item->loadRepertoryItem($categoryId, (int)$id, $markupId, $this->getUser());
        }
        return $this->json();
    }
}