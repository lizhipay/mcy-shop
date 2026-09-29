<?php
declare (strict_types=1);

namespace Kernel\Context\CLI;

class Request extends \Kernel\Context\Abstract\Request
{

    /**
     * @param \Swoole\Http\Request $request
     * @throws \ReflectionException
     */
    public function __construct(\Swoole\Http\Request $request)
    {
        $this->post = (array)$request->post;
        $this->method = strtoupper($request->getMethod());
        $this->get = (array)$request->get;
        $this->header = $this->parseHeader($request->header);

        $this->cookie = (array)$request->cookie;
        $uri = "/" . trim((string)$request->server['request_uri'], "/");
        $uris = explode(".", $uri);
        $this->uri = (string)$uris[0];
        $this->uriSuffix = $uris[1] ?? "";
        $this->raw = (string)$request->getContent();
        $this->files = $request->files ?? [];

        //仅在直连对端属于可信代理时才采纳 X-Forwarded-For / X-Real-IP，否则一律使用对端真实 IP，
        //杜绝任意客户端通过伪造转发头污染登录/注册日志、绕过自推风控、伪造支付回调来源与后台会话 IP 绑定。
        $this->remoteAddr = (string)($request->server['remote_addr'] ?? "");
        $this->clientIp = $this->resolveClientIp($this->remoteAddr);

        if (str_contains((string)$this->header("ContentType"), "application/json")) {
            $this->json = (array)json_decode($this->raw, true);
        }

        if (isset($this->header['Https']) && strtolower($this->header['Https']) == "on") {
            $this->header['Scheme'] = "https";
        } elseif (!isset($this->header['Scheme'])) {
            $this->header['Scheme'] = "http";
        }

        //基址一律由（经站点域名绑定校验的）Host 推导，绝不采用客户端可任意伪造的 Origin，
        //杜绝支付回跳/异步回调地址被劫持到攻击者域名。
        $this->url = $this->header['Scheme'] . '://' . $this->header['Host'];
        $this->domain = (string)explode(":", (string)$this->header['Host'])[0];

        parent::__construct();
    }


    /**
     * @param array $headers
     * @return array
     */
    private function parseHeader(array $headers): array
    {
        $array = [];
        foreach ($headers as $key => $val) {
            $array[str_replace(" ", "", ucwords(str_replace("-", " ", $key)))] = $val;
        }
        return $array;
    }

    /**
     * 直连对端是否为可信代理；只有可信时才采纳按模式读取的转发头（如 CF-Connecting-IP）。
     * @return bool
     */
    protected function isTrustedPeer(): bool
    {
        return $this->ipInList($this->remoteAddr, $this->getTrustedProxies());
    }

    /**
     * 计算真实客户端 IP：仅当直连对端为可信代理时，才信任转发头。
     * @param string $remoteAddr
     * @return string
     */
    private function resolveClientIp(string $remoteAddr): string
    {
        $trusted = $this->getTrustedProxies();

        //对端不可信：直接采用对端 IP，忽略可被任意伪造的 XFF/XRealIp
        if (!$this->ipInList($remoteAddr, $trusted)) {
            return $remoteAddr !== "" ? $remoteAddr : "0.0.0.0";
        }

        //对端可信：从 X-Forwarded-For 右侧向左取第一个非可信 IP 作为真实客户端
        $xff = (string)($this->header['XForwardedFor'] ?? "");
        if ($xff !== "") {
            foreach (array_reverse(array_map('trim', explode(',', $xff))) as $ip) {
                if ($ip !== "" && filter_var($ip, FILTER_VALIDATE_IP) && !$this->ipInList($ip, $trusted)) {
                    return $ip;
                }
            }
        }

        $xReal = (string)($this->header['XRealIp'] ?? "");
        if ($xReal !== "" && filter_var($xReal, FILTER_VALIDATE_IP)) {
            return $xReal;
        }

        return $remoteAddr !== "" ? $remoteAddr : "0.0.0.0";
    }

    /**
     * 可信代理列表。默认仅信任回环地址；可在 config/server.php 的 trust_proxies 中配置（支持 IP 与 CIDR，"*" 表示全部信任）。
     * @return array
     */
    private function getTrustedProxies(): array
    {
        try {
            $server = (array)\Kernel\Util\Config::get("server");
        } catch (\Throwable $e) {
            $server = [];
        }
        $list = $server['trust_proxies'] ?? ['127.0.0.1', '::1'];
        return is_array($list) ? $list : ['127.0.0.1', '::1'];
    }

    /**
     * @param string $ip
     * @param array $list
     * @return bool
     */
    private function ipInList(string $ip, array $list): bool
    {
        if ($ip === "") {
            return false;
        }
        foreach ($list as $item) {
            $item = (string)$item;
            if ($item === "*") {
                return true;
            }
            if (str_contains($item, "/")) {
                if ($this->cidrMatch($ip, $item)) {
                    return true;
                }
            } elseif ($ip === $item) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    private function cidrMatch(string $ip, string $cidr): bool
    {
        $parts = explode('/', $cidr, 2);
        $subnet = $parts[0];
        if (!isset($parts[1])) {
            return $ip === $subnet;
        }
        $bits = (int)$parts[1];
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);
        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }
        $bytes = intdiv($bits, 8);
        $remainder = $bits % 8;
        if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
            return false;
        }
        if ($remainder > 0) {
            $mask = chr((0xff << (8 - $remainder)) & 0xff);
            return (ord($ipBin[$bytes]) & ord($mask)) === (ord($subnetBin[$bytes]) & ord($mask));
        }
        return true;
    }

}