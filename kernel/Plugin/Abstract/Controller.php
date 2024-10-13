<?php
declare (strict_types=1);

namespace Kernel\Plugin\Abstract;

use App\Model\Manage;
use App\Model\User;
use App\Service\Common\Config;
use App\Service\User\Site;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Entity\Plugin as a;
use Kernel\Session\Session;
use Kernel\Util\Aes;
use Kernel\Util\Context;
use Kernel\Util\Str;

abstract class Controller
{

    use \Kernel\Component\Plugin;

    #[Inject]
    protected Request $request;

    #[Inject]
    protected Response $response;

    #[Inject]
    protected Session $session;

    #[Inject]
    protected Site $site;

    #[Inject]
    protected Config $config;
    /**
     * @var a
     */
    protected a $plugin;


    /**
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $site = Di::instance()->make(Site::class);
        if (!$site->effective()) {
            throw new JSONException("当前域名未绑定任何站点");
        }

        $this->plugin = $this->getPlugin();
    }

    /**
     * 检测是否在USR环境中
     * @return bool
     */
    protected function isUsr(): bool
    {
        if ($this->plugin->uid == "*") {
            return false;
        }
        return true;
    }

    /**
     * @return User|null
     */
    protected function getUser(): ?User
    {
        return Context::get(User::class);
    }

    /**
     * @return Manage|null
     */
    protected function getManage(): ?Manage
    {
        return Context::get(Manage::class);
    }

    /**
     * 渲染模版
     * @param string $template
     * @param string|null $title
     * @param array $data
     * @param array $paths
     * @return Response
     * @throws \ReflectionException
     */
    public function render(string $template, ?string $title = null, array $data = [], array $paths = []): Response
    {
        $data['plugin'] = [
            'config' => $this->plugin->getConfig(),
            'all' => $this->plugin
        ];
        $data['language'] = strtolower(Context::get(\Kernel\Language\Entity\Language::class)->preferred);
        $data['ccy_symbol'] = $this->config->getCurrency()->symbol;
        $data['user'] = $this->getUser();
        $data['manage'] = $this->getManage();
        $data['group'] = $this->getUser()?->group?->toArray() ?? [];

        $var = Di::instance()->get(Site::class);
        $var->setTemplateData($data);

        /**
         * @var Response $response
         */
        $response = Context::get(Response::class);
        return $response->end()->render($template, $title, $data, array_merge([$this->plugin->path . "/View/"], $paths));
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
        /**
         * @var Response $response
         */
        $response = Context::get(Response::class);
        $json = $response->json($code, $message, $data, $ext)->getOptions("json");
        return $response->withHeader("Content-Type", "text/plain; charset=utf-8")->withHeader("Secret", $secret)->raw(Aes::encrypt($json, $key, $key));
    }
}