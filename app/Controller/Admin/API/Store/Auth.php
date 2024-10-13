<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Store;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Validator\Store\Captcha;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Plugin;
use Kernel\Validator\Method;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Auth extends Base
{

    #[Inject]
    private \App\Service\Store\Auth $auth;


    /**
     * @param string $type
     * @return Response
     */
    #[Validator([[Captcha::class, "type"]], Method::GET)]
    public function captcha(string $type): Response
    {
        return $this->response->raw($this->auth->captcha($type))->withHeader("Content-Type", "image/png");
    }


    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\Store\Auth::class, ["loginUsername", "loginPassword", "captcha"]]
    ])]
    public function login(): Response
    {
        $login = $this->auth->login($this->request->post("username"), $this->request->post("password"), $this->request->post("captcha"));
        Plugin::inst()->setStoreUser($login->id, $login->key, "main");
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\Store\Auth::class, ["registerUsername", "registerPassword", "code", "phone", "captcha"]]
    ])]
    public function register(): Response
    {
        $login = $this->auth->register($this->request->post("username"), $this->request->post("password"), $this->request->post("phone"), $this->request->post("code"), $this->request->post("captcha"));
        Plugin::inst()->setStoreUser($login->id, $login->key, "main");
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\Store\Auth::class, ["phone", "registerPassword", "code", "captcha"]]
    ])]
    public function reset(): Response
    {
        $login = $this->auth->reset($this->request->post("phone"), $this->request->post("password"), $this->request->post("code"), $this->request->post("captcha"));
        Plugin::inst()->setStoreUser($login->id, $login->key, "main");
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Store\Auth::class, ["sendSms", "type", "captcha"]]
    ])]
    public function sendSms(): Response
    {
        $this->auth->sendSms(type: $this->request->post("type"), phone: $this->request->post("phone"), captcha: $this->request->post("captcha"));
        return $this->json();
    }
}