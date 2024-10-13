<?php
declare (strict_types=1);

namespace App\Service\Common;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\Upload::class)]
interface Upload
{
    /**
     * @param string $path
     * @param string $type
     * @param int|null $userId
     * @return void
     */
    public function add(string $path, string $type, ?int $userId = null): void;


    /**
     * @param string $hash
     * @return string|null
     */
    public function get(string $hash): ?string;


    /**
     * @param string $path
     * @return void
     */
    public function remove(string $path): void;
}