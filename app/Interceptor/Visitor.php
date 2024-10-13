<?php
declare (strict_types=1);

namespace App\Interceptor;

use App\Const\Cookie;
use App\Service\Common\Config;
use App\Service\User\Lifetime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interface\Interceptor;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Language\Language;
use Kernel\Util\Context;
use Kernel\Util\Date;

class Visitor implements Interceptor
{

    #[Inject]
    private Lifetime $lifetime;

    #[Inject]
    private Config $config;

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     * @throws \ReflectionException
     */
    public function handle(Request $request, Response $response, int $type): Response
    {
        $userToken = base64_decode((string)$request->cookie(Cookie::USER_TOKEN));
        $head = \Kernel\Util\JWT::inst()->getHead($userToken);

        $config = $this->config->getMainConfig("site");

        if (!isset($head['uid'])) {
            $config['force_login'] == 1 && ($response = $this->login($request, $response, $type));
            return $response;
        }

        $user = \App\Model\User::find($head['uid']);

        if (!$user || !$userToken) {
            $config['force_login'] == 1 && ($response = $this->login($request, $response, $type));
            return $response;
        }

        try {
            $jwt = JWT::decode($userToken, new Key($user->password, 'HS256'));
        } catch (\Exception $e) {
            $config['force_login'] == 1 && ($response = $this->login($request, $response, $type));
            return $response;
        }

        $loginTime = $this->lifetime->get($user->id, "last_login_time");

        if ($jwt->expire <= time() || $loginTime != $jwt->loginTime || $user->status != 1) {
            $config['force_login'] == 1 && ($response = $this->login($request, $response, $type));
            return $response;
        }

        $this->lifetime->update($user->id, "last_active_time", Date::current());

        Context::set(\App\Model\User::class, $user);
        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     * @throws \ReflectionException
     */
    private function login(Request $request, Response $response, int $type): Response
    {
        $response->withCookie(Cookie::USER_TOKEN, "", 0);
        if ($type == \Kernel\Annotation\Interceptor::API) {
            return $response->end()->json(0, Language::instance()->output("网站需要登录才能访问"));
        } else {
            $a = http_build_query($request->get());
            return $response->end()->render(
                template: "302.html",
                data: ["url" => "/login?goto=" . urlencode($request->uri() . ($a ? "?" . $a : "")), "time" => 1, "message" => Language::instance()->output("网站需要登录才能访问")]
            );
        }
    }
}