<?php
declare(strict_types=1);

namespace Kernel\Context\Interface;

interface File
{

    /**
     * @return string
     */
    public function getFileName(): string;

    /**
     * @return string
     */
    public function getMime(): string;

    /**
     * @return string
     */
    public function getTmp(): string;

    /**
     * @return int
     */
    public function getError(): int;

    /**
     * @return int
     */
    public function getSize(): int;

    /**
     * @return string
     */
    public function getSuffix(): string;

    /**
     * @param string $path
     * @param array $ext
     * @param int $size
     * @param string $dir
     * @return string
     */
    public function save(string $path, array $ext = ['jpg', 'png', 'jpeg', 'ico', 'gif', 'mp4', 'zip', 'woff', 'woff2', 'ttf', 'otf'], int $size = 10240, string $dir = BASE_PATH): string;
}