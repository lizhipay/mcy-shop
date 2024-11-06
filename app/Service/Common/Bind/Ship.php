<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use App\Const\RepertoryItemSkuCache;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryOrder;
use Kernel\Container\Di;
use Kernel\Plugin\Usr;

class Ship implements \App\Service\Common\Ship
{


    /**
     * @return \App\Service\Common\RepertoryItemSku
     * @throws \ReflectionException
     */
    private function getRepertoryItemSkuService(): \App\Service\Common\RepertoryItemSku
    {
        return Di::inst()->make(\App\Service\Common\RepertoryItemSku::class);
    }

    /**
     * @param int $repertoryItemSkuId
     * @param RepertoryOrder|null $order
     * @return \Kernel\Plugin\Handle\Ship|null
     * @throws \ReflectionException
     */
    public function getShip(int $repertoryItemSkuId, ?RepertoryOrder $order = null): ?\Kernel\Plugin\Handle\Ship
    {
        // TODO: 这里的代码需要进行缓存优化，待完善

        /**
         * @var RepertoryItemSku $repertoryItemSku
         */
        $repertoryItemSku = RepertoryItemSku::with(["repertoryItem"])->find($repertoryItemSkuId);
        if (!$repertoryItemSku) {
            return null;
        }

        /**
         * @var RepertoryItem $repertoryItem
         */
        $repertoryItem = $repertoryItemSku->repertoryItem;
        if (!$repertoryItem) {
            return null;
        }

        $env = Usr::instance()->userToEnv($repertoryItem->user_id);


        /**
         * @var \Kernel\Plugin\Abstract\Ship $ship
         */
        return \Kernel\Plugin\Ship::instance()->getShipHandle($repertoryItem->plugin, $env, $repertoryItem, $repertoryItemSku, $order);
    }

    /**
     * @param int $repertoryItemSkuId
     * @param int $action
     * @return string
     * @throws \ReflectionException
     */
    public function stock(int $repertoryItemSkuId, int $action = RepertoryItemSkuCache::ACTION_READ_CACHE): string
    {
        $repertoryItemSku = $this->getRepertoryItemSkuService();
        $cache = $repertoryItemSku->getCache($repertoryItemSkuId, RepertoryItemSkuCache::TYPE_STOCK);
        if ($cache !== null && $action === RepertoryItemSkuCache::ACTION_READ_CACHE) {
            return $cache;
        }

        $ship = $this->getShip($repertoryItemSkuId);
        if (!$ship) {
            $stock = "0";
        } else {
            $stock = (string)$ship->stock();
        }

        $repertoryItemSku->setCache($repertoryItemSkuId, RepertoryItemSkuCache::TYPE_STOCK, $stock);
        return $stock;
    }


    /**
     * @param int $repertoryItemSkuId
     * @param array $map
     * @return bool
     * @throws \ReflectionException
     */
    public function inspection(int $repertoryItemSkuId, array $map = []): bool
    {
        $ship = $this->getShip($repertoryItemSkuId);
        if (!$ship) {
            $state = false;
        } else {
            $state = $ship->inspection($map);
        }
        return $state;
    }

    /**
     * @param int $repertoryItemSkuId
     * @param int $quantity
     * @param int $action
     * @return bool
     * @throws \ReflectionException
     */
    public function hasEnoughStock(int $repertoryItemSkuId, int $quantity = 1, int $action = RepertoryItemSkuCache::ACTION_READ_CACHE): bool
    {
        $repertoryItemSku = $this->getRepertoryItemSkuService();
        $cache = $repertoryItemSku->getCache($repertoryItemSkuId, RepertoryItemSkuCache::TYPE_HAS_ENOUGH_STOCK);
        if ($cache !== null && $action === RepertoryItemSkuCache::ACTION_READ_CACHE) {
            return (bool)$cache;
        }
        $ship = $this->getShip($repertoryItemSkuId);
        if (!$ship) {
            $state = false;
        } else {
            $state = $ship->hasEnoughStock($quantity);
        }
        $repertoryItemSku->setCache($repertoryItemSkuId, RepertoryItemSkuCache::TYPE_HAS_ENOUGH_STOCK, $state ? "1" : "0");
        return $state;
    }
}