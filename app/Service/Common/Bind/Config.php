<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use App\Entity\Config\Currency;
use App\Model\Site;
use App\Model\User;
use Kernel\Container\Memory;
use Kernel\Context\Interface\Request;
use Kernel\Exception\NotFoundException;
use Kernel\Util\Arr;
use Kernel\Util\Context;
use App\Model\Config as Model;

class Config implements \App\Service\Common\Config
{

    /**
     * @param string $key
     * @param int|null $userId
     * @return mixed
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function getUserConfig(string $key, ?int $userId = null): mixed
    {
        if (!$userId) {
            /**
             * @var User $user
             */
            $host = Context::get(Request::class)?->header("Host");
            if (!$host) {
                return [];
            }
            $user = Site::getUser((string)$host);
            if (!$user) {
                return [];
            }
            $userId = $user->id;
        }

        $column = Arr::getChainFirst($key);

        $cacheKey = "config_find_sql_{$column}_{$userId}";

        if (Memory::instance()->has($cacheKey)) {
            return Arr::get(Memory::instance()->get($cacheKey), Arr::getChainIgnoreFirst($key));;
        }

        $config = Model::where("key", $column)->where("user_id", $userId)->first();

        if (!$config) {
            return [];
        }

        $cfg = (array)json_decode((string)$config->value, true);

        Memory::instance()->set($cacheKey, $cfg);
        return Arr::get($cfg, Arr::getChainIgnoreFirst($key));
    }


    /**
     * @param string $key
     * @return mixed
     * @throws \ReflectionException
     */
    public function getMainConfig(string $key): mixed
    {
        $column = Arr::getChainFirst($key);

        $cacheKey = "config_find_sql_" . $column;
        if (Memory::instance()->has($cacheKey)) {
            return Arr::get(Memory::instance()->get($cacheKey), Arr::getChainIgnoreFirst($key));
        }

        $cfg = [];
        $config = Model::where("key", $column)->whereNull("user_id")->first();
        if ($config) {
            $cfg = (array)json_decode((string)$config->value, true);
            Memory::instance()->set($cacheKey, $cfg);
        }
        return Arr::get($cfg, Arr::getChainIgnoreFirst($key));
    }

    /**
     * @param string $key
     * @param int|null $userId
     * @return mixed
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function getUserOrMainConfig(string $key, ?int $userId = null): mixed
    {
        return $this->getUserConfig($key, $userId) ?: $this->getMainConfig($key);
    }

    /**
     * @return Currency
     * @throws \ReflectionException
     */
    public function getCurrency(): Currency
    {
        $var = Context::get(Currency::class);
        if ($var) {
            return $var;
        }
        $list = \Kernel\Util\Config::get("currency");
        $payConfig = $this->getMainConfig("pay");
        $currency = $payConfig['currency'] ?: "rmb";
        if (!isset($list[$currency])) {
            $currency = "rmb";
        }
        $config = $list[$currency];
        $entity = new Currency($currency, $config['symbol'], $config['name']);
        $entity->setRate($payConfig['exchange_rate'] ?? "999999");
        Context::set(Currency::class, $entity);
        return $entity;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getAsyncUrl(): string
    {
        $payCfg = $this->getMainConfig("pay");
        if (isset($payCfg['async_custom'], $payCfg['async_host']) && $payCfg['async_custom'] == 1) {
            return $payCfg['async_protocol'] . "://" . $payCfg['async_host'];
        }
        return (string)Context::get(Request::class)?->url();
    }
}