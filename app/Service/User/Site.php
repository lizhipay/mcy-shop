<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Entity\Site\NginxInfo;
use App\Model\User;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Site::class)]
interface Site
{
    /***
     * @param int $themePage
     * @param string $template
     * @param array $data
     * @return array
     */
    public function bind(int $themePage, string $template, array &$data): array;


    /**
     * @param array $data
     * @return void
     */
    public function setTemplateData(array &$data): void;


    /**
     * @return bool
     */
    public function effective(): bool;


    /**
     * @return array
     */
    public function getMainDomains(): array;


    /**
     * @param User $user
     * @param int $type
     * @param string $domain
     * @param string $subdomain
     * @param string $pem
     * @param string $key
     * @return void
     */
    public function add(User $user, int $type, string $domain, string $subdomain = "", string $pem = "", string $key = ""): void;


    /**
     * @param string $domain
     * @param string $pem
     * @param string $key
     * @return void
     */
    public function modifyCertificate(string $domain, string $pem, string $key): void;


    /**
     * @param string $domain
     * @return array
     */
    public function getCertificate(string $domain): array;

    /**
     * @param string $domain
     * @return void
     */
    public function del(string $domain): void;


    /**
     * @param string $key
     * @param string|null $userId
     * @return mixed
     */
    public function getConfig(string $key, ?string $userId = null): array;


    /**
     * @param string $host
     * @return array
     */
    public function getDnsRecord(string $host): array;


    /**
     * @param string $host
     * @return NginxInfo
     */
    public function getNginxInfo(string $host): NginxInfo;


    /**
     * @param NginxInfo $nginxInfo
     * @param string $proxyPass
     * @param string|null $conf
     * @return string
     */
    public function getNginxProxyConfig(NginxInfo $nginxInfo, string $proxyPass, ?string $conf = null): string;
}