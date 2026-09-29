<?php
declare(strict_types=1);

namespace App\Service\Common\Bind;

use GuzzleHttp\Exception\GuzzleException;
use Kernel\Annotation\Inject;
use Kernel\Exception\HandleException;
use Kernel\Exception\ServiceException;
use Kernel\Util\File;
use Kernel\Util\Http;
use Kernel\Util\Str;

class Image implements \App\Service\Common\Image
{

    #[Inject]
    private \App\Service\Common\Upload $upload;

    /**
     * 远程请求安全选项：限制协议、限制重定向次数并逐跳校验目标，防止通过 3xx 跳转绕过 SSRF 防护。
     * @return array
     */
    private function safeHttpOptions(): array
    {
        return [
            "timeout" => 15,
            "allow_redirects" => [
                "max" => 3,
                "strict" => true,
                "referer" => false,
                "protocols" => ["http", "https"],
                "on_redirect" => function ($request, $response, $uri) {
                    $this->assertSafeRemoteUrl((string)$uri);
                },
            ],
        ];
    }

    /**
     * SSRF 防护：校验远程 URL 仅为 http(s)，且解析出的所有 IP 均为公网地址，
     * 拒绝指向环回/私网/保留/链路本地/CGNAT 的地址，杜绝借“远程图片下载”探测或攻击内网。
     * @param string $url
     * @return void
     * @throws ServiceException
     */
    private function assertSafeRemoteUrl(string $url): array
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            throw new ServiceException("非法的图片地址");
        }
        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            throw new ServiceException("图片地址协议不被允许");
        }

        $host = trim($parts['host'], "[]"); //去除 IPv6 方括号
        $ips = [];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
        } else {
            $v4 = @gethostbynamel($host);
            if (is_array($v4)) {
                $ips = array_merge($ips, $v4);
            }
            $recs = @dns_get_record($host, DNS_AAAA);
            if (is_array($recs)) {
                foreach ($recs as $r) {
                    if (!empty($r['ipv6'])) {
                        $ips[] = $r['ipv6'];
                    }
                }
            }
        }

        if (empty($ips)) {
            throw new ServiceException("无法解析图片地址主机，已拒绝");
        }

        foreach ($ips as $ip) {
            if (!$this->isPublicIp($ip)) {
                throw new ServiceException("图片地址指向内网/保留地址，已拒绝(SSRF)");
            }
        }

        return $ips;
    }

    /**
     * 校验 URL 安全后，返回把连接“钉死”到已验证公网 IP 的 Guzzle/curl 选项，
     * 杜绝校验通过后 DNS 重绑定(rebinding)在实际连线时改指内网(TOCTOU SSRF)。
     * @param string $url
     * @return array
     * @throws ServiceException
     */
    private function pinnedHttpOptions(string $url): array
    {
        $ips = $this->assertSafeRemoteUrl($url);
        $opt = $this->safeHttpOptions();
        $parts = parse_url($url);
        $host = trim((string)($parts['host'] ?? ""), "[]");
        //主机为域名（非 IP 字面量）时才需要钉住解析结果
        if ($host !== "" && !filter_var($host, FILTER_VALIDATE_IP) && !empty($ips)) {
            $ipList = implode(",", $ips);
            $opt['curl'] = ($opt['curl'] ?? []) + [
                CURLOPT_RESOLVE => ["{$host}:443:{$ipList}", "{$host}:80:{$ipList}"],
            ];
        }
        return $opt;
    }

    /**
     * @param string $ip
     * @return bool
     */
    private function isPublicIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }
        //额外拦截 CGNAT 100.64.0.0/10
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            if ($long === false) {
                return false;
            }
            if (($long & 0xffc00000) === (ip2long("100.64.0.0") & 0xffc00000)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $imagePath
     * @param int $newHeight
     * @param string $basePath
     * @return bool|string
     */
    public function createThumbnail(string $imagePath, int $newHeight, string $basePath = BASE_PATH): bool|string
    {
        $baseImagePathInfo = pathinfo($imagePath);
        $thumbPath = $baseImagePathInfo['dirname'] . '/thumb/' . $baseImagePathInfo['basename'];

        if (is_file($basePath . $thumbPath)) {
            return $thumbPath;
        }

        $imageDiskPath = $basePath . $imagePath;

        list($width, $height) = getimagesize($imageDiskPath);

        if ($newHeight >= $height) {
            return $imagePath;
        }

        $imageType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        $source = null;
        switch ($imageType) {
            case 'jpg':
            case 'jpeg':
                $source = @imagecreatefromjpeg($imageDiskPath);
                break;
            case 'gif':
                $source = @imagecreatefromgif($imageDiskPath);
                break;
            case 'png':
                $source = @imagecreatefrompng($imageDiskPath);
                break;
            case 'webp':
                $source = @imagecreatefromwebp($imageDiskPath);
                break;
            default:
                return false;
        }

        if (!$source) {
            return false;
        }

        $newWidth = (int)($width / $height * $newHeight);

        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $pathInfo = pathinfo($imageDiskPath);
        $thumbnailDirectory = $pathInfo['dirname'] . '/thumb/';

        if (!file_exists($thumbnailDirectory)) {
            if (!mkdir($thumbnailDirectory, 0755, true)) {
                return false;
            }
        }

        $thumbnailPath = $thumbnailDirectory . $pathInfo['basename'];
        switch ($imageType) {
            case 'jpg':
            case 'jpeg':
                if (!imagejpeg($thumb, $thumbnailPath)) {
                    imagedestroy($thumb);
                    imagedestroy($source);
                    return false;
                }
                break;
            case 'gif':
                if (!imagegif($thumb, $thumbnailPath)) {
                    imagedestroy($thumb);
                    imagedestroy($source);
                    return false;
                }
                break;
            case 'png':
                if (!imagepng($thumb, $thumbnailPath)) {
                    imagedestroy($thumb);
                    imagedestroy($source);
                    return false;
                }
                break;
            case 'webp':
                if (!imagewebp($thumb, $thumbnailPath)) {
                    imagedestroy($thumb);
                    imagedestroy($source);
                    return false;
                }
                break;
        }

        imagedestroy($thumb);
        imagedestroy($source);

        return $thumbPath;
    }


    /**
     * @param string $filePath
     * @return bool
     */
    public function isRealImage(string $filePath): bool
    {
        $imageInfo = getimagesize($filePath);
        if ($imageInfo !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $url
     * @return string
     */
    public function getImageExtensionFromURL(string $url): string
    {
        // 解析 URL 获取路径部分
        $path = parse_url($url, PHP_URL_PATH);
        return strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * @param $url
     * @return bool
     * @throws GuzzleException
     */
    public function isRealImageFromURL($url): bool
    {
        $response = Http::make()->head($url, $this->pinnedHttpOptions((string)$url));
        $mimeType = $response->getHeaderLine('Content-Type');
        $validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($mimeType, $validImageTypes)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $url
     * @param bool $isCreateThumbnail
     * @param int|null $userId
     * @return array
     * @throws GuzzleException
     * @throws ServiceException
     */
    public function downloadRemoteImage(string $url, bool $isCreateThumbnail = true, ?int $userId = null): array
    {
        $this->assertSafeRemoteUrl($url);
        $extension = $this->getImageExtensionFromURL($url);

        if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            throw new ServiceException("检测到[$url]不是一张有效的图片");
        }

        if (!$this->isRealImageFromURL($url)) {
            throw new ServiceException("检测到[{$url}]不是一张图片，风险较高，请慎重接入！");
        }

        $imagePath = "/assets/static/" . ($userId > 0 ? $userId : "general") . "/image/";
        $unique = $imagePath . date("Y-m-d/") . Str::generateRandStr() . ".{$extension}";

        $dir = dirname(BASE_PATH . $unique);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        Http::make()->get($url, array_merge(["sink" => BASE_PATH . $unique], $this->pinnedHttpOptions($url)));
        if (!is_file(BASE_PATH . $unique)) {
            throw new ServiceException("图片下载失败：$url");
        }

        if (!$this->isRealImage(BASE_PATH . $unique)) {
            File::remove(BASE_PATH . $unique);
            throw new ServiceException("检测到[{$url}]伪造成一张图片诱导本程序进行远程下载，风险极高，此文件已删除并粉碎！");
        }

        $hash = md5_file(BASE_PATH . $unique);
        $cache = $this->upload->get($hash);

        if ($cache) {
            if ($isCreateThumbnail) {
                $baseImagePathInfo = pathinfo($cache);
                $thumbPath = $baseImagePathInfo['dirname'] . '/thumb/' . $baseImagePathInfo['basename'];
                return [$cache, file_exists(BASE_PATH . $thumbPath) ? $thumbPath : $cache];
            }
            return [$cache];
        }

        if ($isCreateThumbnail) {
            $thumbUrl = $this->createThumbnail($unique, 128);
            if (!$thumbUrl) {
                if (is_file(BASE_PATH . $unique)) {
                    File::remove(BASE_PATH . $unique);
                }
                throw new ServiceException("缩略图生成失败：{$url}");
            }

            $this->upload->add($unique, "image", $userId);
            return [$unique, $thumbUrl];
        }
        return [$unique];
    }
}