<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

class HookInfo
{

    public string $namespace;
    public string $method;
    public string $name;
    public Plugin $plugin;
    public string $env;
    public int $point;
    public int $weight;

    public function __construct(string $name, string $namespace, string $method, Plugin $plugin, string $env, int $point, int $weight = 100)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->method = $method;
        $this->plugin = $plugin;
        $this->env = $env;
        $this->point = $point;
        $this->weight = $weight;
    }
} 