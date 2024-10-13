<?php
declare (strict_types=1);

namespace App\Service\Store;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Store\Bind\Http::class)]
interface Http
{
    /**
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * @return array
     */
    public function ping(): array;

    /**
     * @param int $index
     * @return void
     */
    public function setNode(int $index): void;

    /**
     * @return int
     */
    public function getNode(): int;

    /**
     * @param string $url
     * @param array $data
     * @param Authentication|null $authentication
     * @return \App\Entity\Store\Http
     */
    public function request(string $url, array $data = [], ?Authentication $authentication = null): \App\Entity\Store\Http;

    /**
     * @param string $url
     * @param string $path
     * @param Authentication|null $authentication
     * @param string $method
     * @param array $data
     * @return bool
     */
    public function download(string $url, string $path, ?Authentication $authentication = null, string $method = "GET", array $data = []): bool;


    /**
     * @param string $mime
     * @param string $file
     * @param Authentication|null $authentication
     * @return \App\Entity\Store\Http
     */
    public function upload(string $mime, string $file, ?Authentication $authentication = null): \App\Entity\Store\Http;
}