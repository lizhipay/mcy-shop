<?php
declare (strict_types=1);

namespace App\Service\Store\Bind;

use App\Entity\Store\Login;
use Kernel\Annotation\Inject;
use Kernel\Exception\ServiceException;

class Auth implements \App\Service\Store\Auth
{

    #[Inject]
    private \App\Service\Store\Http $http;

    /**
     * @param string $type
     * @return string
     * @throws ServiceException
     */
    public function captcha(string $type): string
    {
        $http = $this->http->request("/auth/captcha", ["type" => $type]);

        if (!isset($http->data["raw"])) {
            throw new ServiceException("图形验证码获取失败");
        }
        return base64_decode($http->data["raw"]);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $captcha
     * @return Login
     * @throws ServiceException
     */
    public function login(string $username, string $password, string $captcha): Login
    {
        $http = $this->http->request("/auth/login",
            [
                "username" => $username,
                "password" => $password,
                "captcha" => $captcha
            ]
        );

        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }

        return new Login($http->data);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $phone
     * @param string $code
     * @param string $captcha
     * @return Login
     * @throws ServiceException
     */
    public function register(string $username, string $password, string $phone, string $code, string $captcha): Login
    {
        $http = $this->http->request("/auth/register",
            [
                "username" => $username,
                "password" => $password,
                "phone" => $phone,
                "code" => $code,
                "captcha" => $captcha
            ]
        );

        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }

        return new Login($http->data);
    }


    /**
     * @param string $type
     * @param string $phone
     * @param string $captcha
     * @return void
     * @throws ServiceException
     */
    public function sendSms(string $type, string $phone, string $captcha): void
    {
        $http = $this->http->request("/auth/phone/code?type={$type}", ["captcha" => $captcha, "phone" => $phone]);
        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }
    }

    /**
     * @param string $phone
     * @param string $password
     * @param string $code
     * @param string $captcha
     * @return Login
     * @throws ServiceException
     */
    public function reset(string $phone, string $password, string $code, string $captcha): Login
    {
        $http = $this->http->request("/auth/reset",
            [
                "password" => $password,
                "phone" => $phone,
                "code" => $code,
                "captcha" => $captcha
            ]
        );

        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }

        return new Login($http->data);
    }
}