<?php
declare (strict_types=1);

namespace Kernel\Session;

interface Session
{
    /**
     * SESSION ID
     */
    const NAME = "acg_session";

    /**
     * @param string|null $key
     * @return mixed
     */
    public function get(?string $key = null): mixed;


    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void;


    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;


    /**
     * @param string $key
     * @return void
     */
    public function remove(string $key): void;

    /**
     * @return void
     */
    public function clear(): void;


    /**
     * @return string
     */
    public function id(): string;


    /**
     * @return bool
     */
    public function gc(): bool;
}