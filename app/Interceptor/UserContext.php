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
use Kernel\Util\Context;
use Kernel\Util\Date;

class UserContext implements Interceptor
{
    #[Inject]
    private Lifetime $lifetime;

    public function handle(Request $request, Response $response, int $type): Response
    {
        $userToken = base64_decode((string)$request->cookie(Cookie::USER_TOKEN));
        $head = \Kernel\Util\JWT::inst()->getHead($userToken);

        if (!isset($head['uid'])) {
            return $response;
        }

        $user = \App\Model\User::find($head['uid']);

        if (!$user || !$userToken) {
            return $response;
        }

        try {
            $jwt = JWT::decode($userToken, new Key($user->password, 'HS256'));
        } catch (\Exception $e) {
            return $response;
        }

        $loginTime = $this->lifetime->get($user->id, "last_login_time");

        if ($jwt->expire <= time() || $loginTime != $jwt->loginTime || $user->status != 1) {
            return $response;
        }

        $this->lifetime->update($user->id, "last_active_time", Date::current());

        Context::set(\App\Model\User::class, $user);
        return $response;
    }
}