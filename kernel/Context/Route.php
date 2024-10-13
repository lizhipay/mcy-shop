<?php
declare (strict_types=1);

namespace Kernel\Context;

class Route implements \Kernel\Context\Interface\Route
{
    private string $route;
    private string $class;
    private string $method;
    private string $action;


    /**
     * @return string
     */
    public function route(): string
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function class(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @param string $default
     * @return string
     */
    public function action(string $default = "*"): string
    {
        if ($this->action == "*") {
            return $default;
        }

        return $this->action;
    }


    /**
     * @param string $route
     */
    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }
}