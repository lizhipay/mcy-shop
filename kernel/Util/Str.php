<?php
declare(strict_types=1);

namespace Kernel\Util;


class Str
{

    /**
     * @param int $scale
     * @param string|int|float ...$amount
     * @return bool
     */
    public static function equalAmount(int $scale, string|int|float ...$amount): bool
    {
        if (count($amount) < 2) {
            return true;
        }

        $normalized = array_map(
            fn(string $item) => (new Decimal($item, $scale))->getAmount($scale),
            $amount
        );

        return count(array_unique($normalized)) === 1;
    }

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
        //使用 CSPRNG 生成随机十六进制串（供会话ID、client_id、app_key、API Secret 等使用），
        //替代原 md5(uniqid()+mt_rand()) 的可预测实现，防止会话ID/令牌被预测。
        if ($length < 1) {
            $length = 1;
        }
        try {
            $hex = bin2hex(random_bytes((int)ceil($length / 2)));
        } catch (\Throwable $e) {
            //极端情况下的兜底（正常不会触发）
            $hex = md5(uniqid((string)mt_rand(), true) . random_int(PHP_INT_MIN, PHP_INT_MAX));
            while (strlen($hex) < $length) {
                $hex .= md5($hex . microtime());
            }
        }
        return substr($hex, 0, $length);
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

    /**
     * 安全执行用户/商家自定义正则匹配：限制模式与主题长度、转义 # 分隔符（杜绝分隔符/修饰符注入）、
     * 降低回溯步数上限（缓解灾难性回溯 ReDoS，Swoole 下 preg_match 会阻塞 worker）。匹配成功返回 true。
     * @param string $pattern 用户提供的正则（不含分隔符）
     * @param string $subject
     * @return bool
     */
    public static function safeRegexMatch(string $pattern, string $subject): bool
    {
        if ($pattern === "" || strlen($pattern) > 300 || strlen($subject) > 5000) {
            return false;
        }
        $delimited = '#' . str_replace('#', '\#', $pattern) . '#';
        $backup = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', '200000');
        try {
            $r = @preg_match($delimited, $subject);
        } finally {
            ini_set('pcre.backtrack_limit', $backup === false ? '1000000' : (string)$backup);
        }
        return $r === 1;
    }

}