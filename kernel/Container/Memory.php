<?php
declare(strict_types=1);

namespace Kernel\Container;

use Kernel\Component\Singleton;
use Kernel\Context\App;
use Swoole\Coroutine;

class Memory
{
    use Singleton;

    /**
     * @var array
     */
    private array $memory = [];


    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        if (!App::$cli) {
            $this->memory[$key] = $value;
            return;
        }
        $cid = Coroutine::getCid();
        if ($cid > 0) {
            $this->memory[$key][$cid] = $value;
            \Swoole\Coroutine\defer(function () use ($key, $cid) {
                unset($this->memory[$key][$cid]);
            });
        } else {
            $this->memory[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        if (!App::$cli) {
            return $this->memory[$key];
        }

        $cid = Coroutine::getCid();
        if ($cid > 0) {
            return $this->memory[$key][$cid];
        } else {
            return $this->memory[$key];
        }
    }


    /**
     * @param string ...$key
     * @return void
     */
    public function del(string ...$key): void
    {
        if (!App::$cli) {
            foreach ($key as $k) {
                unset($this->memory[$k]);
            }
            return;
        }
        $cid = Coroutine::getCid();
        foreach ($key as $k) {
            if ($cid > 0) {
                unset($this->memory[$k][$cid]);
            } else {
                unset($this->memory[$k]);
            }
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (!App::$cli) {
            return isset($this->memory[$key]);
        }
        $cid = Coroutine::getCid();

        if ($cid > 0) {
            return isset($this->memory[$key][$cid]);
        } else {
            return isset($this->memory[$key]);
        }
    }
}