<?php
declare (strict_types=1);

namespace Kernel\Plugin\Abstract;

use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Entity\Plugin;

abstract class Command extends \Kernel\Console\Command
{
    /**
     * @var Plugin|null
     */
    private ?Plugin $plugin = null;


    /**
     * @throws RuntimeException
     */
    protected function getPlugin(): ?Plugin
    {

        if ($this->plugin) {
            return $this->plugin;
        }

        $extend = $this->command->getExtend();

        if (!is_array($extend) || !isset($extend["name"]) || !isset($extend["env"])) {
            throw new RuntimeException("未知插件");
        }

        $plugin = \Kernel\Plugin\Plugin::instance()->getPlugin($extend['name'], $extend['env']);

        if (!$plugin) {
            throw new RuntimeException(sprintf("插件[%s]不存在", $extend['name']));
        }

        $this->plugin = $plugin;
        return $plugin;
    }
}