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

namespace Hyperf\Engine\Contract;

use ArrayObject;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;

interface CoroutineInterface
{
    /**
     * @param callable $callable [required]
     */
    public function __construct(callable $callable);

    /**
     * @param mixed ...$data
     */
    public function execute(...$data): static;

    public function getId(): int;

    /**
     * @param callable $callable [required]
     * @param mixed ...$data
     * @return $this
     */
    public static function create(callable $callable, ...$data): static;

    /**
     * @return int returns coroutine id from current coroutine, -1 in non coroutine environment
     */
    public static function id(): int;

    /**
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     * @throws RunningInNonCoroutineException when running in non-coroutine context
     * @throws CoroutineDestroyedException when the coroutine has been destroyed
     */
    public static function pid(?int $id = null): int;

    /**
     * Set config to coroutine.
     */
    public static function set(array $config): void;

    /**
     * @param null|int $id coroutine id
     */
    public static function getContextFor(?int $id = null): ?ArrayObject;

    /**
     * Execute callback when coroutine destruct.
     */
    public static function defer(callable $callable): void;

    /**
     * Yield the current coroutine.
     * @param mixed $data only Support Swow
     * @return bool|mixed Swow:mixed, Swoole:bool
     */
    public static function yield(mixed $data = null): mixed;

    /**
     * Resume the coroutine by coroutine Id.
     * @param mixed $data only Support Swow
     * @return bool|mixed Swow:mixed, Swoole:bool
     */
    public static function resumeById(int $id, mixed ...$data): mixed;

    /**
     * Get the coroutine stats.
     */
    public static function stats(): array;

    /**
     * Check if a coroutine exists or not.
     */
    public static function exists(int $id): bool;
}
