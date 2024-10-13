<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;


use App\Service\Common\Config;
use Kernel\Annotation\Inject;
use Kernel\Util\Http;

class Ip implements \App\Service\Common\Ip
{


    #[Inject]
    private Config $config;

    /**
     * @param string $ip
     * @return string
     */
    public function getLocation(string $ip): string
    {
        $config = $this->config->getUserOrMainConfig("site");
        if (isset($config['is_get_location']) && $config['is_get_location'] == 1) {
            try {
                //TODO: 这里需要植入插件hook位

                $response = Http::make(['timeout' => 3])->get("https://whois.pconline.com.cn/ipJson.jsp?ip={$ip}&json=true");
                $contents = (array)json_decode((string)$response->getBody()->getContents(), true);
                return $contents['addr'] ?? "未知";
            } catch (\Throwable $e) {
                return "未知";
            }
        }
        return "未知";
    }
}