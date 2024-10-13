<?php
declare (strict_types=1);

namespace App\Service\Store\Bind;

use App\Entity\Store\Authentication;
use GuzzleHttp\Client;
use Kernel\Annotation\Inject;
use Kernel\Context\Interface\Request;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Plugin;
use Kernel\Util\Aes;
use Kernel\Util\Config;
use Kernel\Util\Context;
use Kernel\Util\Date;
use Kernel\Util\File;
use Kernel\Util\Str;

class Http implements \App\Service\Store\Http
{

    #[Inject]
    private Client $httpClient;

    /**
     * @var array
     */
    private array $baseUrl = [];

    private string $node = BASE_PATH . "/runtime/store.node";

    public function __construct()
    {
        $this->baseUrl = json_decode(Aes::decrypt("S4ZsanWgMxRvaVXdokGRlll9zI9vNjmG5Rulrg1B5DfnC2SJNgOtEts6B0Ze3bBKmgHvmP1OIL1e2s2tfjAgY1izCqc/+W3YDVPEzIbjDIcKBuTo2S36wgAUHXYwmH3OIChueP3C+Qn7jPYG81kPCg==", "7DBFE206DC2702AE", "CA0666E4E91DDDBC"), true);
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl[$this->getNode()];
    }

    /**
     * @return array
     */
    public function ping(): array
    {
        $pings = [];
        foreach ($this->baseUrl as $url) {
            try {
                $start = Date::timestamp();
                \Kernel\Util\Http::make(["timeout" => 3])->post($url . "/ping");
                $pings[] = Date::timestamp() - $start;
            } catch (\Throwable $e) {
                $pings[] = 0;
            }
        }
        return $pings;
    }

    /**
     * @param int $index
     * @return void
     */
    public function setNode(int $index): void
    {
        File::write($this->node, (string)$index);
    }

    /**
     * @return int
     */
    public function getNode(): int
    {
        return File::read($this->node, function (string $content) {
            return (int)$content;
        }) ?: 0;
    }


    /**
     * @return string
     */
    private
    function getSenderIp(): string
    {
        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);
        if (!$request) {
            return "127.0.0.1";
        }
        return $request->clientIp();
    }


    /**
     * @param string $url
     * @param array $data
     * @param Authentication|null $authentication
     * @return \App\Entity\Store\Http
     * @throws RuntimeException
     * @throws ServiceException
     */
    public
    function request(string $url, array $data = [], ?Authentication $authentication = null): \App\Entity\Store\Http
    {
        $data['project_name'] = "mcy-shop";
        $secret = Str::generateRandStr(32);
        $key = substr($secret, 0, 16);
        $signature = Str::generateSignature($data, $secret);
        $body = Aes::encrypt(json_encode($data), $key, $key);
        try {
            $channel = @Config::get("channel");
            $headers = [
                "Secret" => $secret,
                "Signature" => $signature,
                "Content-Type" => "text/plain",
                "Hwid" => Plugin::inst()->getHwId(),
                "Sender" => $this->getSenderIp(),
                "Channel" => $channel['id'] ?? 0
            ];
            $store = Plugin::inst()->getStoreUser("main");
            if ($authentication && $store) {
                $headers["Token"] = "{$authentication->id}@{$authentication->key}@" . $store->id . "@" . ($authentication->substation ? "substation" : "main");
            }
            $response = $this->httpClient->post($this->getBaseUrl() . $url, [
                "verify" => false,
                "timeout" => 10,
                "body" => $body,
                "headers" => $headers
            ]);
        } catch (\Throwable $e) {
            throw new ServiceException("连接应用商店失败#0");
        }

        $contents = $response->getBody()->getContents();

        if (!$contents) {
            throw new ServiceException("连接应用商店失败#1");
        }

        $_secret = $response->getHeader("Secret")[0] ?? null;

        if (!$_secret || strlen($_secret) != 32) {
            $json = json_decode($contents, true);
            throw new ServiceException($json['msg'] ?? "连接应用商店失败#2");
        }

        $_key = substr($_secret, 0, 16);
        $_data = (array)json_decode((string)Aes::decrypt($contents, $_key, $_key), true);

        if (!isset($_data["code"]) || !isset($_data["msg"])) {
            throw new ServiceException("连接应用商店失败#3");
        }

        return new \App\Entity\Store\Http((int)$_data["code"], $_data["msg"], $_data["data"] ?? [], $_data);
    }


    /**
     * @param string $url
     * @param string $path
     * @param Authentication|null $authentication
     * @param string $method
     * @param array $data
     * @return bool
     */
    public
    function download(string $url, string $path, ?Authentication $authentication = null, string $method = "GET", array $data = []): bool
    {
        try {
            $data['project_name'] = "mcy-shop";
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $secret = Str::generateRandStr(32);
            $key = substr($secret, 0, 16);
            $signature = Str::generateSignature($data, $secret);
            $body = Aes::encrypt(json_encode($data), $key, $key);

            $headers = [
                "Secret" => $secret,
                "Signature" => $signature,
                "Content-Type" => "text/plain",
                "Hwid" => Plugin::inst()->getHwId(),
                "Sender" => $this->getSenderIp()
            ];

            $store = Plugin::inst()->getStoreUser("main");
            if ($authentication && $store) {
                $headers["Token"] = "{$authentication->id}@{$authentication->key}@" . $store->id . "@" . ($authentication->substation ? "substation" : "main");
            }

            $options = [
                "verify" => false,
                "sink" => $path,
                "headers" => $headers
            ];

            if ($method === "POST" && !empty($data)) {
                $options["body"] = $body;
            }

            if (!preg_match("/^(http:\/\/|https:\/\/)/", $url)) {
                $url = $this->getBaseUrl() . $url;
            }

            $this->httpClient->request($method, $url, $options);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $mime
     * @param string $file
     * @param Authentication|null $authentication
     * @return \App\Entity\Store\Http
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public
    function upload(string $mime, string $file, ?Authentication $authentication = null): \App\Entity\Store\Http
    {
        $headers = ["Hwid" => Plugin::inst()->getHwId(), "Sender" => $this->getSenderIp()];

        $store = Plugin::inst()->getStoreUser("main");
        if ($authentication && $store) {
            $headers["Token"] = "{$authentication->id}@{$authentication->key}@" . $store->id . "@" . ($authentication->substation ? "substation" : "main");
        }

        try {
            $response = $this->httpClient->request("POST", $this->getBaseUrl() . "/user/upload?mime={$mime}", [
                "verify" => false,
                "multipart" => [
                    [
                        "name" => "file",
                        "contents" => fopen($file, "r")
                    ]
                ],
                "headers" => $headers
            ]);
        } catch (\Throwable $e) {
            throw new ServiceException("文件上传失败#0");
        }

        $contents = $response->getBody()->getContents();
        $json = json_decode($contents, true);

        if (!isset($json['code']) || $json['code'] != 200) {
            throw new ServiceException($json['msg'] ?? "文件上传失败#1");
        }
        return new \App\Entity\Store\Http($json['code'], $json['msg'], $json['data'] ?? [], $json);
    }

}