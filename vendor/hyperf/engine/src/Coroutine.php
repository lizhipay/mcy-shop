<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Engine;

use ArrayObject;
use Hyperf\Engine\Contract\CoroutineInterface;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;
use Hyperf\Engine\Exception\RuntimeException;
use Swoole\Coroutine as SwooleCo;

class Coroutine implements CoroutineInterface
{
    /**
     * @var callable
     */
    private $callable;

    private ?int $id = null;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public static function create(callable $callable, ...$data): static
    {
        $coroutine = new static($callable);
        $coroutine->execute(...$data);
        return $coroutine;
    }

    public function execute(...$data): static
    {
        $this->id = SwooleCo::create($this->callable, ...$data);
        return $this;
    }

    public function getId(): int
    {
        if (is_null($this->id)) {
            throw new RuntimeException('Coroutine was not be executed.');
        }
        return $this->id;
    }

    public static function id(): int
    {
        return SwooleCo::getCid();
    }

    public static function pid(?int $id = null): int
    {
        if ($id) {
            $cid = SwooleCo::getPcid($id);
            if ($cid === false) {
                throw new CoroutineDestroyedException(sprintf('Coroutine #%d has been destroyed.', $id));
            }
        } else {
            $cid = SwooleCo::getPcid();
        }
        if ($cid === false) {
            throw new RunningInNonCoroutineException('Non-Coroutine environment don\'t has parent coroutine id.');
        }
        return max(0, $cid);
    }

    public static function set(array $config): void
    {
        SwooleCo::set($config);
    }

    public static function getContextFor(?int $id = null): ?ArrayObject
    {
        if ($id === null) {
            return SwooleCo::getContext();
        }

        return SwooleCo::getContext($id);
    }

    public static function defer(callable $callable): void
    {
        SwooleCo::defer($callable);
    }

    /**
     * Yield the current coroutine.
     * @param mixed $data only Support Swow
     * @return bool
     */
    public static function yield(mixed $data = null): mixed
    {
        return SwooleCo::yield();
    }

    /**
     * Resume the coroutine by coroutine Id.
     * @param mixed $data only Support Swow
     * @return bool
     */
    public static function resumeById(int $id, mixed ...$data): mixed
    {
        return SwooleCo::resume($id);
    }

    /**
     * Get the coroutine stats.
     */
    public static function stats(): array
    {
        return SwooleCo::stats();
    }

    public static function exists(?int $id = null): bool
    {
        return SwooleCo::exists($id);
    }
}
