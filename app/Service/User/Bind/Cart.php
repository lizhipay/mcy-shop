<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Entity\Shop\Sku;
use App\Model\CartItem;
use App\Model\OrderItem;
use App\Model\RepertoryItemSku;
use App\Model\User;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Exception\JSONException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Util\Date;
use Kernel\Util\Decimal;
use Kernel\Util\Str;

class Cart implements \App\Service\User\Cart
{

    #[Inject]
    private \App\Service\User\Order $order;

    #[Inject]
    private \App\Service\User\Item $item;

    /**
     * @param User|null $customer
     * @param string $clientId
     * @return \App\Model\Cart
     */
    private function getCart(?User $customer, string $clientId): \App\Model\Cart
    {
        $cart = \App\Model\Cart::query();
        if ($customer) {
            $cart = $cart->where("customer_id", $customer->id)->first();
        } else {
            $cart = $cart->where("client_id", $clientId)->first();
        }

        if (!$cart) {
            $cart = new \App\Model\Cart();
            $customer && $cart->customer_id = $customer->id;
            $cart->client_id = $clientId;
            $cart->create_time = Date::current();
            $cart->save();
        }

        return $cart;
    }


    /**
     * @param User|null $customer
     * @param string $clientId
     * @return string
     */
    public function getClientId(?User $customer, string $clientId): string
    {
        if (!$customer) {
            return $clientId;
        }
        if (!\App\Model\Cart::query()->where("client_id", $clientId)->where("customer_id", "!=", $customer->id)->exists()) {
            return $clientId;
        }
        return Str::generateRandStr(32);
    }

    /**
     * @param User|null $customer
     * @param string $clientId
     * @return array
     * @throws \Exception
     */
    public function getItems(?User $customer, string $clientId): array
    {
        $cart = $this->getCart($customer, $clientId);
        $cartItems = CartItem::with(['sku' => function (HasOne $hasOne) {
            $hasOne->with(['item' => function (HasOne $hasOne) {
                $hasOne->with(['repertoryItem']);
            }]);
        }])->where("cart_id", $cart->id)->get();
        $items = [];
        foreach ($cartItems as $item) {
            if ($item->sku && $item->sku->item) {
                $cartItem = new \App\Entity\Shop\CartItem($item);
                $itemEntity = new \App\Entity\Shop\Item($item->sku->item);
                $itemEntity->setWidget((array)json_decode((string)$item?->sku?->item?->repertoryItem?->widget, true));
                $cartItem->setItem($itemEntity);
                $cartItem->setSku(new Sku($item->sku));
                $items[] = $cartItem->toArray();
            } else {
                $item->delete();
            }
        }
        return $items;
    }

    /**
     * @param User|null $customer
     * @param string $clientId
     * @return string
     */
    public function getAmount(?User $customer, string $clientId): string
    {
        $cart = $this->getCart($customer, $clientId);
        return (string)CartItem::query()->where("cart_id", $cart->id)->sum("amount");
    }

    /**
     * @param User|null $customer
     * @param string $clientId
     * @param int $quantity
     * @param int $skuId
     * @param array $option
     * @return bool
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function add(?User $customer, string $clientId, int $quantity, int $skuId, array $option): bool
    {
        $cart = $this->getCart($customer, $clientId);
        $sku = $this->item->getSku($skuId);

        /**
         * 仓库的商品对象
         * @var RepertoryItemSku $repertoryItemSku
         */
        $repertoryItemSku = $sku?->repertoryItemSku;

        if (!$repertoryItemSku) {
            throw new ServiceException("货源不存在");
        }

        if ($repertoryItemSku?->repertoryItem->status != 2) {
            throw new ServiceException("商品源暂不可用");
        }

        Plugin::instance()->unsafeMultiHook([$sku?->user_id, $repertoryItemSku?->user_id], Point::SERVICE_CART_ADD_BEFORE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $option, $cart, $sku, $repertoryItemSku, $customer, $quantity);

        /**
         * @var CartItem $cartItem
         */
        $cartItem = CartItem::query()->where("cart_id", $cart->id)->where("sku_id", $skuId)->first();

        if ($cartItem) {
            $this->inspectQuantityRestriction($quantity + $cartItem->quantity, $skuId, $repertoryItemSku, $sku?->user_id, $clientId, $customer);
            //如果信息相同，则增量
            if (json_encode($option) == json_encode(is_array($cartItem->option) ? $cartItem->option : [])) {
                $cartItem->quantity = $cartItem->quantity + $quantity;
                $cartItem->save();
                return true;
            }
        } else {
            $this->inspectQuantityRestriction($quantity, $skuId, $repertoryItemSku, $sku?->user_id, $clientId, $customer);
        }

        $cartItem = new CartItem();
        $cartItem->cart_id = $cart->id;
        $cartItem->quantity = $quantity;
        $cartItem->sku_id = $skuId;
        $cartItem->option = $option;
        $cartItem->create_time = Date::current();
        $cartItem->price = $this->order->getAmount($customer, $sku, $quantity);
        $cartItem->amount = (new Decimal($cartItem->price))->mul((string)$quantity)->getAmount(2);
        $cartItem->save();

        Plugin::instance()->unsafeMultiHook([$sku?->user_id, $repertoryItemSku?->user_id], Point::SERVICE_CART_ADD_AFTER, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $option, $cart, $cartItem, $sku, $repertoryItemSku, $customer, $quantity);
        return true;
    }


    /**
     * @param int $quantity
     * @param int $skuId
     * @param RepertoryItemSku $repertoryItemSku
     * @param int|null $merchantId
     * @param string $clientId
     * @param User|null $customer
     * @return void
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function inspectQuantityRestriction(int $quantity, int $skuId, RepertoryItemSku $repertoryItemSku, ?int $merchantId, string $clientId, ?User $customer = null): void
    {
        /**
         * @var \App\Service\User\Item $itemService
         */
        $itemService = Di::inst()->make(\App\Service\User\Item::class);

        $quantityRestriction = $itemService->getQuantityRestriction($merchantId, $repertoryItemSku);

        if ($quantityRestriction->min > $quantity) {
            throw new ServiceException(sprintf("购买数量不能低于 %d 件", $quantityRestriction->min));
        }

        if ($quantityRestriction->max < $quantity && $quantityRestriction->max != 0) {
            throw new ServiceException(sprintf("购买数量不能大于 %d 件", $quantityRestriction->max));
        }

        if ($quantityRestriction->total != 0) {
            $totalPurchases = OrderItem::query()
                ->leftJoin("order", "order_item.order_id", "=", "order.id")
                ->where("order_item.sku_id", $skuId)
                ->where("order.status", 1);

            if ($customer) {
                $totalPurchases = $totalPurchases->where("order.customer_id", $customer->id);
            } else {
                $totalPurchases = $totalPurchases->where("order.client_id", $clientId);
            }

            $totalPurchases = $totalPurchases->sum("quantity");

            if (($totalPurchases + $quantity) > $quantityRestriction->total) {
                throw new ServiceException(sprintf("该商品最多购买 %d 件，您暂时已经无法进行购买", $quantityRestriction->total));
            }
        }
    }

    /**
     * @param User|null $customer
     * @param string $clientId
     * @param int $itemId
     * @param int $quantity
     * @return void
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function changeQuantity(?User $customer, string $clientId, int $itemId, int $quantity): void
    {
        $cart = $this->getCart($customer, $clientId);
        $cartItem = CartItem::with(["sku"])->where("cart_id", $cart->id)->find($itemId);
        if (!$cartItem) {
            return;
        }

        if (!$cartItem->sku) {
            return;
        }


        /**
         * 仓库的商品对象
         * @var RepertoryItemSku $repertoryItemSku
         */
        $repertoryItemSku = $cartItem?->sku?->repertoryItemSku;

        if (!$repertoryItemSku) {
            throw new ServiceException("货源不存在");
        }

        if ($cartItem?->sku?->repertoryItemSku?->repertoryItem->status != 2) {
            throw new ServiceException("商品源暂不可用");
        }

        $totalQuantity = CartItem::query()->where("cart_id", $cart->id)->where("sku_id", $cartItem->sku_id)->sum("quantity");
        $this->inspectQuantityRestriction(($totalQuantity - $cartItem->quantity) + $quantity, $cartItem->sku_id, $repertoryItemSku, $cartItem?->sku?->user_id, $clientId, $customer);

        $cartItem->price = $this->order->getAmount($customer, $cartItem->sku, $quantity);
        $cartItem->quantity = $quantity;
        $cartItem->amount = (new Decimal($cartItem->price))->mul((string)$quantity)->getAmount(2);
        $cartItem->save();
    }

    /**
     * @param User|null $customer
     * @param string $clientId
     * @param int $itemId
     * @return bool
     * @throws \Exception
     */
    public function del(?User $customer, string $clientId, int $itemId): bool
    {
        $cart = $this->getCart($customer, $clientId);
        $cartItem = CartItem::query()->where("cart_id", $cart->id)->find($itemId);
        if (!$cartItem) {
            return false;
        }
        return (bool)($cartItem->delete());
    }

    /**
     * @param User|null $customer
     * @param string $clientId
     * @return void
     */
    public function clear(?User $customer, string $clientId): void
    {
        $cart = $this->getCart($customer, $clientId);
        CartItem::query()->where("cart_id", $cart->id)->delete();
    }

    /**
     * @param User $customer
     * @param string $clientId
     * @return void
     */
    public function bindUser(User $customer, string $clientId): void
    {
        //更新购物车
        $cart = \App\Model\Cart::query()->where("customer_id", $customer->id)->orderBy("id", "asc")->first();
        if ($cart) {
            $carts = \App\Model\Cart::query()->where("client_id", $clientId)->whereNull("customer_id")->get();
            foreach ($carts as $c) {
                CartItem::query()->where("cart_id", $c->id)->update(["cart_id" => $cart->id]);
                $c->delete();
            }
        } else {
            \App\Model\Cart::query()->where("client_id", $clientId)->whereNull("customer_id")->update(["customer_id" => $customer->id]);
        }
        //更新订单
        \App\Model\Order::query()->where("client_id", $clientId)->whereNull("customer_id")->update(['customer_id' => $customer->id]);
    }


    /**
     * @param User|null $customer
     * @param string $clientId
     * @param int $itemId
     * @return \App\Entity\Shop\CartItem
     * @throws JSONException
     * @throws \Exception
     */
    public function getItem(?User $customer, string $clientId, int $itemId): \App\Entity\Shop\CartItem
    {
        $cart = $this->getCart($customer, $clientId);
        $cartItem = CartItem::query()->where("cart_id", $cart->id)->find($itemId);
        if (!$cartItem) {
            throw new JSONException("物品不存在");
        }

        if (!$cartItem->sku || !$cartItem->sku->item) {
            $cartItem->delete();
            throw new JSONException("SKU不存在");
        }

        $itemEntity = new \App\Entity\Shop\Item($cartItem->sku->item);
        $itemEntity->setWidget(json_decode($cartItem->sku->item->repertoryItem->widget, true));

        $cartItemEntity = new \App\Entity\Shop\CartItem($cartItem);
        $cartItemEntity->setItem($itemEntity);

        return $cartItemEntity;
    }

    /**
     * @param User|null $customer
     * @param string $clientId
     * @param int $itemId
     * @param array $option
     * @return void
     * @throws JSONException
     */
    public function updateOption(?User $customer, string $clientId, int $itemId, array $option): void
    {
        $cart = $this->getCart($customer, $clientId);
        $cartItem = CartItem::query()->where("cart_id", $cart->id)->find($itemId);
        if (!$cartItem) {
            throw new JSONException("物品不存在");
        }

        $cartItem->option = array_merge($cartItem->option, $option);

        $cartItem->save();
    }
}