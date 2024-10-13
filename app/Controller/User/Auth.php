<?php
declare (strict_types=1);

namespace App\Controller\User;

use App\Const\Cookie;
use App\Model\Config;
use Kernel\Annotation\Inject;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RedirectException;
use Kernel\Plugin\Const\Theme;


class Auth extends Base
{

    #[Inject]
    private \App\Service\Common\Config $config;

    /**
     * @return Response
     * @throws RedirectException
     */
    public function register(): Response
    {
        if ($this->request->cookie(Cookie::USER_TOKEN)) {
            return $this->response->redirect("/user/trade/order");
        }

        $config = Config::main("register");

        if ($config['status'] != 1) {
            throw (new RedirectException("注册暂时已关闭"))->setTime(3)->setUrl("/login");
        }

        return $this->theme(Theme::REGISTER, "Auth/Register.html", "注册账号", [
            "option" => $config
        ]);
    }

    /**
     * @return Response
     */
    public function login(): Response
    {
        if ($this->request->cookie(Cookie::USER_TOKEN)) {
            return $this->response->redirect("/user/trade/order");
        }

        $config = Config::main("register");

        return $this->theme(Theme::LOGIN, "Auth/Login.html", "登录账号", ["option" => $config]);
    }


    /**
     * @return Response
     * @throws RedirectException
     */
    public function reset(): Response
    {
        if ($this->request->cookie(Cookie::USER_TOKEN)) {
            return $this->response->redirect("/user/trade/order");
        }

        $config = Config::main("register");

        if (!isset($config['email_reset_state']) || $config['email_reset_state'] != 1) {
            throw (new RedirectException("功能未启用"))->setTime(3)->setUrl("/login");
        }

        return $this->theme(Theme::LOGIN, "Auth/Reset.html", "重置密码");
    }


    /**
     * @param int $type
     * @return Response
     * @throws JSONException
     */
    public function terms(int $type): Response
    {
        $titles = [
            0 => "用户协议",
            1 => "隐私政策",
            2 => "商品服务协议"
        ];
        if (!in_array($type, [0, 1, 2])) {
            throw new JSONException("没有找到相关服务条款");
        }

        $config = $this->config->getMainConfig("register");
        $content = "";
        switch ($type) {
            case 0:
                $content = $config['user_agreement'];
                break;
            case 1:
                $content = $config['privacy_policy'];
                break;
            case 2:
                $content = $config['service_agreement'];
                break;
        }

        return $this->theme(Theme::TERMS, "Auth/Terms.html", $titles[$type], ["content" => $content]);
    }
}