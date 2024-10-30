<?php
declare (strict_types=1);

namespace App\Service\Common;

use App\Entity\Config\Currency;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\Config::class)]
interface Config
{

    /**
     * @param string $key
     * @param int|null $userId
     * @return mixed
     */
    public function getUserConfig(string $key, ?int $userId = null): mixed;


    /**
     * @param string $key
     * @return mixed
     */
    public function getMainConfig(string $key): mixed;


    /**
     * @param string $key
     * @param int|null $userId
     * @return mixed
     */
    public function getUserOrMainConfig(string $key, ?int $userId = null): mixed;


    /**
     * @return Currency
     */
    public function getCurrency(): Currency;


    /**
     * @return string
     */
    public function getAsyncUrl(): string;
}