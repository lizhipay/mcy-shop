<?php
declare (strict_types=1);

namespace Kernel\Context;

class Command implements \Kernel\Context\Interface\Command
{

    private string $command;
    private string $class;
    private string $method;
    private mixed $extend = null;

    private ?string $name = null;
    private ?string $desc = null;

    /**
     * @param string $command
     * @param string $class
     * @param string $method
     * @param mixed|null $extend
     * @param string|null $name
     * @param string|null $desc
     */
    public function __construct(string $command, string $class, string $method, mixed $extend = null , ?string $name = null , ?string $desc = null)
    {
        $this->command = $command;
        $this->class = $class;
        $this->method = $method;
        $this->extend = $extend;
        $this->name = $name;
        $this->desc = $desc;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getExtend(): mixed
    {
        return $this->extend;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDesc(): ?string
    {
        return $this->desc;
    }
}