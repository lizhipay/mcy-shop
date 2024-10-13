<?php
declare(strict_types=1);

namespace App\Controller\User\API\Config;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\Site as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class Site extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function get(): Response
    {
        $get = new Get(Model::class);
        $get->setWhere($this->request->post());
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->where("user_id", $this->getUser()->id);
        });
        return $this->json(data: ["list" => $data]);
    }


    /**
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Site::class, "type"]
    ])]
    public function save(): Response
    {
        $type = $this->request->post("type", Filter::INTEGER);
        $domain = trim((string)$this->request->post("domain")); //域名后缀
        $subdomain = trim((string)$this->request->post("subdomain")); //域名前缀
        $privateDomain = trim((string)$this->request->post("private_domain")); //独立域名
        $pem = trim((string)$this->request->post("pem")); //证书
        $key = trim((string)$this->request->post("key")); //秘钥

        if ($type == \App\Const\Site::TYPE_DOMAIN) {
            $domain = $privateDomain;
        }
        $this->site->add($this->getUser(), $type, $domain, $subdomain, $pem, $key);
        return $this->json();
    }


    /**
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Site::class, ["existsDomain", "pem", "key"]]
    ])]
    public function modifyCertificate(): Response
    {
        $domain = trim((string)$this->request->post("domain"));
        $pem = trim((string)$this->request->post("pem"));
        $key = trim((string)$this->request->post("key"));
        $this->site->modifyCertificate($domain, $pem, $key);
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Site::class, "existsDomain"]
    ])]
    public function getCertificate(): Response
    {
        $domain = trim((string)$this->request->post("domain"));
        return $this->json(data: $this->site->getCertificate($domain));
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Site::class, "existsDomain"]
    ])]
    public function del(): Response
    {
        $domain = trim((string)$this->request->post("domain"));
        $this->site->del($domain);
        return $this->json(message: "删除成功");
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function getDnsRecord(): Response
    {
        $dnsValue = trim((string)$this->_config->getMainConfig("subdomain.dns_value"));
        $domains = (array)$this->request->post("domains");
        $arr = [];
        foreach ($domains as $domain) {
            if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && !preg_match("/^(\*\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/", $domain)) {
                continue;
            }
            $records = $this->site->getDnsRecord($domain);
            $arr[$domain] = [
                "records" => $this->site->getDnsRecord($domain),
                "status" => in_array($dnsValue, $records) ? 1 : 0
            ];
        }
        return $this->json(data: $arr);
    }
}