<?php
declare (strict_types=1);

namespace Kernel\Plugin\Handle;

use Kernel\Plugin\Entity\Item;

interface ForeignShip
{

    /**
     * 获取外部商品列表
     * @return Item[]
     */
    public function getItems(): array;


    /**
     * @param string $uniqueId
     * @param array $options
     * @return Item|null
     */
    public function getItem(string $uniqueId, array $options = []): ?Item;
}