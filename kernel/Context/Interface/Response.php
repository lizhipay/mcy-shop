<?php
declare (strict_types=1);

namespace Kernel\Context\Interface;

interface Response
{
    /**
     * 重定向
     */
    const TYPE_REDIRECT = 0x1;

    /**
     * 渲染JSON
     */
    const TYPE_JSON = 0x2;

    /**
     * 渲染视图
     */
    const TYPE_RENDER = 0x3;


    /**
     * 渲染原始数据
     */
    const TYPE_RAW = 0x4;

    /**
     * @param string $key
     * @param string $value
     * @param int $expire
     * @return $this
     */
    public function withCookie(string $key, string $value, int $expire): static;


    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function withHeader(string $key, string $value): static;


    /**
     * @param string $url
     * @return $this
     */
    public function redirect(string $url): static;

    /**
     * @param int $code
     * @param string $message
     * @param array|null $data
     * @param array $ext
     * @return $this
     */
    public function json(int $code = 200, string $message = "success", ?array $data = null, array $ext = []): static;


    /**
     * @param string $template
     * @param string|null $title
     * @param array $data
     * @param string|array $path
     * @return $this
     */
    public function render(string $template, ?string $title = null, array $data = [], string|array $path = BASE_PATH . "/app/View/"): static;


    /**
     * @param string $data
     * @return $this
     */
    public function raw(string $data): static;


    /**
     * @param string|null $key
     * @return mixed
     */
    public function getOptions(?string $key = null): mixed;


    /**
     * @return void
     */
    public function draw(): void;


    /**
     * @return $this
     */
    public function end(): static;
}