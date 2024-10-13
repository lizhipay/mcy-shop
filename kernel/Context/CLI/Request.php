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

        $xForwardedFor = $this->header['XForwardedFor'] ?? "";
        if ($xForwardedFor) {
            $arr = explode(',', $xForwardedFor);
            $this->clientIp = (string)$arr[0];
        } else {
            $this->clientIp = $this->header['XRealIp'] ?? $request->server['remote_addr'];
        }

        if (str_contains((string)$this->header("ContentType"), "application/json")) {
            $this->json = (array)json_decode($this->raw);
        }

        if (isset($this->header['Https']) && strtolower($this->header['Https']) == "on") {
            $this->header['Scheme'] = "https";
        } elseif (!isset($this->header['Scheme'])) {
            $this->header['Scheme'] = "http";
        }

        $this->url = $this->header['Origin'] ?? $this->header['Scheme'] . '://' . $this->header['Host'];
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

}