<?php
declare (strict_types=1);

namespace App\Controller\User\Config;

use App\Controller\User\Base;
use App\Interceptor\Merchant;
use App\Interceptor\User;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;
use Kernel\Util\Arr;

#[Interceptor(class: [User::class, Merchant::class])]
class Config extends Base
{

    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function index(): Response
    {

        $mainConfig = \App\Model\Config::main("subdomain");

        $dict = [];
        $data = Arr::strToList($mainConfig['subdomain'], "\n");

        foreach ($data as $domain) {
            if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                $dict[] = ["id" => $domain, "name" => $domain];
            }
        }

        return $this->theme(Theme::USER_CONFIG, "Config/Config.html", "店铺设置", [
            "subdomain" => [
                "dict" => $dict,
                'dns_type' => $mainConfig['dns_type'],
                'dns_value' => $mainConfig['dns_value'],
                'dns_status' => (bool)$mainConfig['dns_status']
            ]
        ]);
    }
}