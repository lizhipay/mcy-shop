<?php
declare (strict_types=1);

namespace App\Controller\User\API;

use App\Const\Cookie;
use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Validator\Method;

#[Interceptor(class: [PostDecrypt::class], type: Interceptor::API)]
#[Validator([[Common::class, "clientId"]], Method::COOKIE)]
class Auth extends Base
{


    #[Inject]
    private \App\Service\User\Auth $auth;

    #[Inject]
    private \App\Service\Common\Config $config;

    /**
     * @param string $type
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Auth::class, "sendEmail"]
    ])]
    public function sendEmail(string $type): Response
    {
        $this->auth->sendEmail($type, $this->request->post());
        return $this->json();
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws NotFoundException
     */
    #[Validator([
        [\App\Validator\User\Auth::class, ["registerUsername", "email", "registerPassword"]]
    ])]
    public function register(): Response
    {
        $password = $this->request->post("password");
        $re = $this->request->post("password_re");
        $terms = $this->request->post("terms");


        if ($password != $re) {
            throw new JSONException("两次密码输入不一致");
        }

        if ($this->config->getMainConfig("register.agreement_status") == 1 && $terms != 1) {
            throw new JSONException("请先同意注册协议。");
        }

        $user = $this->auth->register(
            map: $this->request->post(),
            clientId: $this->request->cookie("client_id"),
            ip: $this->request->clientIp(),
            ua: $this->request->header("UserAgent"),
            merchant: $this->getSiteOwner(),
            inviter: $this->getInviter()
        );


        //自动登录
        $config = $this->config->getMainConfig("site");
        $login = $this->auth->setLoginSuccess($user);
        $this->response->withCookie(Cookie::USER_TOKEN, $login, (int)$config['session_expire']);
        return $this->json(data: ["token" => $login]);
    }


    /**
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Auth::class, ["loginUsername", "loginPassword"]]
    ])]
    public function login(): Response
    {
        $login = $this->auth->login($this->request->post(), $this->request->clientIp(), $this->request->header("UserAgent"), $this->request->cookie("client_id"));
        $config = $this->config->getMainConfig("site");
        $this->response->withCookie(Cookie::USER_TOKEN, $login, (int)$config['session_expire']);
        return $this->json(data: ["token" => $login]);
    }

    /**
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Auth::class, ["sendEmail", "resetEmailCode", "registerPassword"]]
    ])]
    public function reset(): Response
    {
        $this->auth->reset($this->request->post());
        return $this->json();
    }
}