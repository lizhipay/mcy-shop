<?php
declare (strict_types=1);

namespace Kernel\Context\Interface;

interface Route
{

    /**
     * @return string
     */
    public function route(): string;


    /**
     * @return string
     */
    public function class(): string;


    /**
     * @return string
     */
    public function method(): string;


    /**
     * @param string $default
     * @return string|null
     */
    public function action(string $default): ?string;


    /**
     * @param string $route
     */
    public function setRoute(string $route): void;

    /**
     * @param string $class
     */
    public function setClass(string $class): void;

    /**
     * @param string $method
     */
    public function setMethod(string $method): void;

    /**
     * @param string $action
     */
    public function setAction(string $action): void;
}