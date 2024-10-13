<?php
declare(strict_types=1);

namespace Kernel\Util;

use Kernel\Exception\JSONException;

class Verify
{

    /**
     * @param mixed $value
     * @param string $message
     * @return void
     * @throws JSONException
     */
    public static function isBlank(mixed $value, string $message): void
    {
        if (!isset($value) || $value === "") {
            throw new JSONException($message);
        }
    }

    /**
     * @param mixed $value
     * @param string $message
     * @return void
     * @throws JSONException
     */
    public static function isNull(mixed $value, string $message): void
    {
        if (is_null($value)) {
            throw new JSONException($message);
        }
    }


    /**
     * @param $url
     * @return bool
     */
    public static function isValidUrl($url): bool
    {
        $pattern = '/^(http:\/\/|https:\/\/)[^\s\/]+(\/[^\s\/]+)*$/';
        return preg_match($pattern, $url) === 1;
    }

    /**
     * @param string $phone
     * @return bool
     */
    public static function isChinaMobile(string $phone): bool
    {
        if (preg_match("/^1[3-9]\d{9}$/", $phone)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $phone
     * @return bool
     */
    public static function isInternationalMobile(string $phone): bool
    {
        if (preg_match('/^\+\d{1,3}\d{6,10}$/', $phone)) {
            return true;
        }
        return false;
    }


    /**
     * @param string $email
     * @return bool
     */
    public static function isEmail(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }


    /**
     * 验证所有值必须相等
     * @param mixed ...$value
     * @return bool
     */
    public static function equals(mixed ...$value): bool
    {
        if (count($value) < 2) {
            return true;
        }
        for ($i = 1; $i < count($value); $i++) {
            if ($value[0] != $value[$i]) {
                return false;
            }
        }
        return true;
    }
}