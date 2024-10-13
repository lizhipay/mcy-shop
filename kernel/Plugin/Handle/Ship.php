<?php
declare(strict_types=1);

namespace Kernel\Plugin\Handle;

interface Ship
{
    /**
     * 交付货物
     * @return string
     */
    public function delivery(): string;

    /**
     * 获取实时库存
     * @return int|string
     */
    public function stock(): int|string;


    /**
     * 库存是否充足
     * @param int $quantity
     * @return bool
     */
    public function hasEnoughStock(int $quantity = 1): bool;


    /**
     * 购买时检查
     * @param array $map
     * @return bool
     */
    public function inspection(array $map): bool;


    /**
     * @return bool
     */
    public function isCustomRender(): bool;

    /**
     * 自定义渲染商品展示，返回HTML代码
     * @return string
     */
    public function render(): string;
}