<?php
declare (strict_types=1);

namespace App\Controller\User;

use App\Entity\Store\Authentication;
use App\Model\User;
use App\Model\UserLoginLog;
use App\Service\Common\Config;
use App\Service\User\Site;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RedirectException;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ViewException;
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
    protected Site $site;

    #[Inject]
    protected Config $_config;

    /**
     * @throws JSONException
     * @throws RedirectException
     * @throws \ReflectionException
     * @throws ViewException
     */
    public function __construct()
    {
        if (!App::$install) {
            $install = new RedirectException("正在初始化安装程序..");
            $install->setTime(0);
            $install->setUrl("/install");
            throw $install;
        }

        $site = Di::instance()->make(Site::class);
        if (!$site->effective()) {
            throw new ViewException("当前域名未绑定，请到网站设置中进行绑定");
        }
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
     * @param int $themePage
     * @param string $template
     * @param string|null $title
     * @param array $data
     * @return Response
     */
    public function theme(int $themePage, string $template, ?string $title = null, array $data = []): Response
    {
        $data["language"] = strtolower(Context::get(\Kernel\Language\Entity\Language::class)->preferred);
        $data['ccy_symbol'] = $this->_config->getCurrency()->symbol;
        $data['user'] = $this->getUser();
        $data['group'] = $this->getUser()?->group?->toArray() ?? [];
        $bind = $this->site->bind($themePage, $template, $data);
        return $this->response->render($bind['template'], $title, $bind['data'], $bind['templatePath']);
    }


    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return Context::get(User::class);
    }

    /**
     * @return string
     */
    public function getUserPath(): string
    {
        return sprintf("/usr/Plugin/M_%d", $this->getUser()->id);
    }

    /**
     * @return User|null
     * @throws NotFoundException
     */
    public function getSiteOwner(): ?User
    {
        return \App\Model\Site::getUser((string)$this->request->header("Host"));
    }


    /**
     * @return Authentication
     * @throws JSONException
     * @throws \ReflectionException
     */
    public function getStoreAuth(): Authentication
    {
        $store = Plugin::inst()->getStoreUser($this->getUserPath());
        if (!$store) {
            throw new JSONException("未登录", 10);
        }
        return $store;
    }

    /**
     * @return User|null
     */
    public function getInviter(): ?User
    {
        $inviteId = $this->getUser() ? $this->getUser()->invite_id : $this->request->cookie("invite_id");
        $ip = $this->request->clientIp();
        if ($inviteId > 0) {
            $user = User::find($inviteId);

            if (!$user) {
                return null;
            }

            if ($user->status == 0) {
                return null;
            }

            //推广人是自己
            if ($user->id == $this->getUser()?->id) {
                return null;
            }


            //检测购买的用户是不是自己
            if (UserLoginLog::query()->where("ip", $ip)->exists()) {
                return null;
            }

            return $user;
        }
        return null;
    }

}