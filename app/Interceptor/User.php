<?php
declare (strict_types=1);

namespace App\Interceptor;

use App\Const\Cookie;
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

class User implements Interceptor
{

    #[Inject]
    private Lifetime $lifetime;

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
        if (!isset($head['uid'])) {
            return $this->login($request, $response, $type);
        }

        $user = \App\Model\User::find($head['uid']);

        if (!$user) {
            return $this->login($request, $response, $type);
        }

        try {
            $jwt = JWT::decode($userToken, new Key($user->password, 'HS256'));
        } catch (\Exception $e) {
            return $this->login($request, $response, $type);
        }

        $loginTime = $this->lifetime->get($user->id, "last_login_time");
        $loginStatus = $this->lifetime->get($user->id, "login_status");

        if ($jwt->expire <= time() || $loginTime != $jwt->loginTime || $user->status != 1 || $loginStatus != 1) {
            return $this->login($request, $response, $type);
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
            return $response->end()->json(0, Language::instance()->output("登录已过期"));
        } else {
            $a = http_build_query($request->get());
            return $response->end()->render(
                template: "302.html",
                data: ["url" => "/login?goto=" . urlencode($request->uri() . ($a ? "?" . $a : "")), "time" => 1, "message" => Language::instance()->output("登录已过期")]
            );
        }
    }
}