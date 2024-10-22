<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Entity\Repertory\RepertoryItemSku;
use App\Model\RepertoryItem;
use App\Model\User;
use App\Service\Common\RepertoryOrder;
use App\Service\Common\Ship;
use Hyperf\Database\Model\Relations\HasMany;
use Kernel\Annotation\Inject;
use Kernel\Exception\JSONException;
use Kernel\Util\Str;

class Supply implements \App\Service\User\Supply
{

    #[Inject]
    private RepertoryOrder $order;

    #[Inject]
    private \App\Service\Common\RepertoryItemSku $sku;

    #[Inject]
    private Ship $ship;

    /**
     * @param User|null $customer
     * @param int $itemId
     * @return \App\Entity\Repertory\RepertoryItem
     * @throws JSONException
     */
    public function getItem(?User $customer, int $itemId): \App\Entity\Repertory\RepertoryItem
    {
        /**
         * @var RepertoryItem $item
         */
        $item = RepertoryItem::with(["sku" => function (HasMany $hasMany) {
            $hasMany->orderBy("sort", "asc");
        }])->find($itemId);

        if (!$item) {
            throw new JSONException("商品不存在");
        }

        if ($item->status != 2) {
            throw new JSONException("商品不可用#0");
        }

        $repertoryItem = new \App\Entity\Repertory\RepertoryItem($item);
        $repertoryItem->setWidget(json_decode($item->widget, true));
        $repertoryItem->setAttr(json_decode($item->attr, true));
        $repertoryItem->setIntroduce($item->introduce);


        $skus = [];
        foreach ($item->sku as $a => $b) {
            $b->stock_price = Str::getAmountStr($this->order->getAmount($customer, $b, 1));
            $repertoryItemSku = new RepertoryItemSku($b);
            $repertoryItemSku->setStock($this->ship->stock($b->id));
            $repertoryItemSku->setWholesale($this->sku->getWholesale($customer, $b->id));
            $repertoryItemSku->haveWholesale === true && $repertoryItem->setHaveWholesale(true);

            if ($this->sku->isDisplay($b, $customer)) {
                $skus[] = $repertoryItemSku;
            }
        }

        if (count($skus) == 0) {
            throw new JSONException("商品不可用#1");
        }

        $repertoryItem->setSkus($skus);
        return $repertoryItem;
    }
}