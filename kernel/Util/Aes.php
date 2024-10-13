<?php
declare(strict_types=1);

namespace Kernel\Util;

use Kernel\Exception\RuntimeException;

/**
 * AES加密解密类
 */
class Aes
{
    /**
     * AES->CBC 加密
     * @param mixed $data
     * @param string $key
     * @param string $iv
     * @param bool $base64
     * @return string
     * @throws RuntimeException
     */
    public static function encrypt(mixed $data, string $key, string $iv, bool $base64 = true): string
    {
        $contents = openssl_encrypt($data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);

        if ($contents === false) {
            throw new RuntimeException("AES-CBC加密出错，请检查您的服务器环境是否支持openssl");
        }

        if ($base64) {
            $contents = base64_encode($contents);
        }

        return $contents;
    }

    /**
     * AES->CBC 解密
     * @param string $data
     * @param string $key
     * @param string $iv
     * @param bool $base64
     * @return string
     */
    public static function decrypt(string $data, string $key, string $iv, bool $base64 = true): string
    {
        if ($base64) {
            $data = base64_decode($data);
        }
        return (string)openssl_decrypt($data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }
}