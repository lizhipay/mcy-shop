<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

class ProcessInfo
{
    public string $name;
    public string $namespace;
    public int $num;
    public Plugin $plugin;
    public string $env;


    /**
     * @param string $name
     * @param string $namespace
     * @param Plugin $plugin
     * @param int $num
     * @param string $env
     */
    public function __construct(string $name, string $namespace, Plugin $plugin, int $num, string $env)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->plugin = $plugin;
        $this->num = $num <= 0 ? 1 : $num;
        $this->env = $env;
    }
}