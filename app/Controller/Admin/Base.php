<?php
declare (strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Store\Authentication;
use App\Model\Config;
use App\Model\Manage;
use App\Service\User\Site;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Language\Entity\Language;
use Kernel\Plugin\Plugin;
use Kernel\Session\Session;
use Kernel\Util\Aes;
use Kernel\Util\Context;
use Kernel\Util\Str;

abstract class Base
{
    #[Inject]
    protected Request $request;

    #[Inject]
    protected Response $response;

    #[Inject]
    protected Session $session;

    #[Inject]
    protected \App\Service\Common\Config $_config;

    /**
     * @throws \ReflectionException
     */
    public function __construct()
    {
        Di::instance()->make(Site::class);
    }


    /**
     * @param int $code
     * @param string $message
     * @param array|null $data
     * @param array $ext
     * @return Response
     * @throws RuntimeException
     */
    public function json(int $code = 200, string $message = "success", ?array $data = null, array $ext = []): Response
    {
        $secret = Str::generateRandStr(32);
        $key = substr($secret, 0, 16);
        $json = $this->response->json($code, $message, $data, $ext)->getOptions("json");
        return $this->response->withHeader("Content-Type", "text/plain; charset=utf-8")->withHeader("Secret", $secret)->raw(Aes::encrypt($json, $key, $key));
    }

    /**
     * 渲染视图
     * @param string $template
     * @param string|null $title
     * @param array $data
     * @return Response
     * @throws \ReflectionException
     */
    public function render(string $template, ?string $title = null, array $data = []): Response
    {
        $data["language"] = strtolower(Context::get(Language::class)->preferred);
        $data['ccy_symbol'] = $this->_config->getCurrency()->symbol;
        $data['site'] = (new \App\Entity\Config\Site(Config::main("site")))->toArray();
        $data['manage'] = $this->getManage();
        return $this->response->render($template, $title, $data, BASE_PATH . "/app/View/Admin/");
    }

    /**
     * @return \App\Controller\Admin\Manage\Manage|null
     */
    public function getManage(): ?Manage
    {
        return Context::get(Manage::class);
    }

    /**
     * @return Authentication
     * @throws JSONException
     * @throws \ReflectionException
     */
    public function getStoreAuth(): Authentication
    {
        $store = Plugin::inst()->getStoreUser("main");
        if (!$store) {
            throw new JSONException("未登录", 10);
        }
        return $store;
    }
}