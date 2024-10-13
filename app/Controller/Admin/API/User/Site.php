<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\User;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Site as Model;
use App\Service\Common\Config;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Site extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private Config $config;

    #[Inject]
    private \App\Service\User\Site $site;


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(): Response
    {
        $map = $this->request->post();
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");

        $raw = [];
        $data = $this->query->get($get, function (Builder $builder) use ($map, &$raw) {
            $raw['site_count'] = (clone $builder)->count();
            return $builder->with([
                'user' => function (HasOne $query) {
                    $query->select(['id', 'username', 'avatar']);
                }
            ]);
        });

        foreach ($data['list'] as &$item) {
            $item['site'] = $this->config->getUserConfig("site", $item['user_id']);
        }

        return $this->json(data: $data, ext: $raw);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function save(): Response
    {
        $save = new Save(Model::class);
        $save->disableAddable();
        $map = $this->request->post();
        $save->setMap($map, ["status"]);
        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->json();
    }

    /**
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Site::class, ["domain", "pem", "key"]]
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
        [\App\Validator\User\Site::class, "domain"]
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
        [\App\Validator\User\Site::class, "domain"]
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
        $dnsValue = trim((string)$this->config->getMainConfig("subdomain.dns_value"));
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