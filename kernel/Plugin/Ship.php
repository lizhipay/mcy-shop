<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryOrder;
use Kernel\Component\Singleton;
use Kernel\Plugin\Handle\ForeignShip as b;
use Kernel\Plugin\Handle\Ship as a;

class Ship
{
    use Singleton;

    /**
     * @param string $name
     * @param string $env
     * @param RepertoryItem $item
     * @param RepertoryItemSku $sku
     * @param RepertoryOrder|null $order
     * @return Ship|null
     * @throws \ReflectionException
     */
    public function getShipHandle(string $name, string $env, RepertoryItem $item, RepertoryItemSku $sku, ?RepertoryOrder $order = null): ?a
    {
        return Plugin::inst()->getHandle($name, $env, a::class, $item, $sku, $order);
    }


    /**
     * @param string $name
     * @param string $env
     * @param array $config
     * @return b|null
     * @throws \ReflectionException
     */
    public function getForeignShipHandle(string $name, string $env, array $config): ?b
    {
        return Plugin::inst()->getHandle($name, $env, b::class, $config);
    }
}