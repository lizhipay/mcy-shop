<?php
declare(strict_types=1);

namespace Kernel\Util;


class Str
{

    /**
     * 生成密码
     * @param string $pass
     * @param string $salt
     * @return string
     */
    public static function generatePassword(string $pass, string $salt): string
    {
        return sha1(md5(md5($pass) . md5($salt)));
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    public static function generateRandStr(int $length = 32): string
    {
        mt_srand();
        $md5 = md5(uniqid(md5((string)time())) . mt_rand(10000, 9999999));
        return substr($md5, 0, $length);
    }

    /**
     * 生成24位订单号
     * @return string
     */
    public static function generateTradeNo(): string
    {
        return date("ymd", time()) . substr((string)\Kernel\Util\Date::timestamp(), -5) . self::generateRandNum(13);
    }

    /**
     * 生成随机数字字符串
     * @param int $length
     * @return string
     */
    public static function generateRandNum(int $length = 24): string
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            try {
                $result .= random_int(0, 9);
            } catch (\Exception $e) {
                $result .= mt_rand(0, 9);
            }
        }
        return $result;
    }

    /**
     * 获取数据签名
     * @param array $data
     * @param string $secret
     * @return string
     */
    public static function generateSignature(array $data, string $secret): string
    {
        unset($data['sign']);
        ksort($data);
        foreach ($data as $key => $val) {
            if ($val === '' || is_array($val)) {
                unset($data[$key]);
            }
        }
        return md5(urldecode(http_build_query($data) . "&key=" . (string)$secret));
    }

    /**
     * @param string|float|int $amount
     * @return string
     */
    public static function getAmountStr(string|float|int $amount): string
    {
        $a = rtrim(rtrim((string)$amount, "0"), ".");
        return $a === "" ? "0.00" : $a;
    }


    /**
     * 驼峰转横杠
     * @param string $input
     * @param string $symbol
     * @return string
     */
    public static function camelToSnake(string $input, string $symbol = "-"): string
    {
        $pattern = '/(?<=\\w)(?=[A-Z])/';
        $snakeCase = preg_replace($pattern, "{$symbol}$1", $input);
        return strtolower($snakeCase);
    }

    /**
     * 横杠转大写驼峰
     * @param string $input
     * @return string
     */
    public static function snakeToPascal(string $input): string
    {
        return str_replace('-', '', ucwords($input, '-'));
    }

    /**
     * 横杠转小写驼峰
     * @param string $input
     * @return string
     */
    public static function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace('-', '', ucwords($input, '-')));
    }

    /**
     * @param int|float|string|null $number
     * @return string
     */
    public static function amountRemoveTrailingZeros(int|float|string|null $number): string
    {
        if (!$number || !is_numeric($number)) {
            return "0.00";
        }
        return preg_replace('/(\.\d*?[1-9])0+|\.0*$/', '$1', (string)$number);
    }

}