<?php
declare (strict_types=1);

namespace Kernel\Plugin\Abstract;

use Kernel\Container\Di;
use Kernel\Plugin\Entity\Plugin;

abstract class ForeignShip implements \Kernel\Plugin\Handle\ForeignShip
{
    //插件信息
    protected Plugin $plugin;
    //货源配置信息
    protected array $config;


    /**
     * @throws \ReflectionException
     */
    public function __construct(Plugin $plugin, array $config)
    {
        Di::inst()->inject($this);
        $this->plugin = $plugin;
        $this->config = $config;
    }
}