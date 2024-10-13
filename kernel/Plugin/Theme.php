<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Component\Singleton;

class Theme
{
    use Singleton;


    /**
     * @param string $name
     * @param string $env
     * @return Entity\Theme|null
     * @throws \ReflectionException
     */
    public function getTheme(string $name, string $env = "/app/Plugin"): ?\Kernel\Plugin\Entity\Theme
    {
        $plugin = Plugin::inst()->getPlugin($name, $env);
        if ($plugin->state['run'] != 1) {
            return null;
        }

        return new \Kernel\Plugin\Entity\Theme($plugin->name, $plugin->theme);
    }

}