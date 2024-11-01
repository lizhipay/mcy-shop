<?php
declare (strict_types=1);

namespace Kernel\Util;

class UserAgent
{
    /**
     * @var array|string[]
     */
    private static array $devices = [
        'iPhone' => 'iPhone',
        'iPad' => 'iPad',
        'iPod' => 'iPod',
        'MI' => '小米手机',
        'Redmi' => '红米手机',
        'Huawei' => '华为手机',
        'HONOR' => '荣耀手机',
        'vivo' => 'VIVO手机',
        'OPPO' => 'OPPO手机',
        'Realme' => 'Realme手机',
        'Samsung' => 'SAMSUNG',
        'LG' => 'LG手机',
        'SONY' => '索尼手机',
        'HTC' => 'HTC手机',
        'Nokia' => '诺基亚',
        'OnePlus' => 'One Plus',
        'Google' => 'Google Mobile',
        'Motorola' => '摩托罗拉',
        'ZTE' => '中兴手机',
        'Lenovo' => '联想手机',
        'Asus' => '华硕手机',
        'BlackBerry' => '黑莓手机',
        'Alcatel' => '阿尔卡特手机',
        'TCL' => 'TCL手机',
        'Meizu' => '魅族手机',
        'Sharp' => '夏普手机',
        'Xperia' => 'SONY Xperia',
        'Pixel' => 'Google Pixel',
        'Macintosh' => 'Mac',
        'Mac OS' => 'Mac',
        'Windows' => 'Windows'
    ];

    /**
     * @param string|null $ua
     * @return bool
     */
    public static function isMobile(?string $ua): bool
    {
        if (!$ua) {
            return false;
        }
        return (bool)preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua);
    }

    /**
     * @param string|null $ua
     * @return bool
     */
    public static function isWeChat(?string $ua): bool
    {
        if (!$ua) {
            return false;
        }
        return preg_match('/MicroMessenger/i', $ua) === 1;
    }

    /**
     * @param string|null $ua
     * @return int
     */
    public static function getEquipment(?string $ua): int
    {
        if (self::isWeChat($ua)) {
            return 3;
        }
        if (self::isMobile($ua)) {
            return 1;
        }
        return 2;
    }

    /**
     * @param string $ua
     * @return string
     */
    public static function getBrowser(string $ua): string
    {
        return match (true) {
            preg_match('/ucweb/i', $ua) => 'UC',
            preg_match('/firefox/i', $ua) => 'Firefox',
            preg_match('/opera/i', $ua) => 'Opera',
            preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua) => 'Safari',
            preg_match('/360se/i', $ua) => '360',
            preg_match('/bidubrowser/i', $ua) => '百度',
            preg_match('/metasr/i', $ua) => '搜狗',
            preg_match('/msie 6.0/i', $ua) => 'IE6',
            preg_match('/msie 7.0/i', $ua) => 'IE7',
            preg_match('/msie 8.0/i', $ua) => 'IE8',
            preg_match('/msie 9.0/i', $ua) => 'IE9',
            preg_match('/msie 10.0/i', $ua) => 'IE10',
            preg_match('/msie 11.0/i', $ua) => 'IE11',
            preg_match('/edg/i', $ua) => 'Edge',
            preg_match('/lbbrowser/i', $ua) => '猎豹',
            preg_match('/micromessenger/i', $ua) => '微信',
            preg_match('/qqbrowser/i', $ua) => 'QQ',
            preg_match('/chrome/i', $ua) && preg_match('/safari/i', $ua) => 'Chrome',
            default => '未知',
        };
    }

    /**
     * @param string $ua
     * @return string
     */
    public static function getDevice(string $ua): string
    {
        foreach (self::$devices as $key => $value) {
            if (stripos($ua, $key) !== false) {
                return $value;
            }
        }

        if (self::isMobile($ua)) {
            return '手机';
        }

        return "PC";
    }
}