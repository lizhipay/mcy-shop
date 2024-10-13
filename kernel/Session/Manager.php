<?php
declare (strict_types=1);

namespace Kernel\Session;

use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Util\Context;
use Kernel\Util\Str;
use Swoole\Timer;

class Manager
{
    use Singleton;

    /**
     * @return void
     */
    public function create(): void
    {
        if (!App::$cli) {
            session_name(Session::NAME);
            session_start();
            //return;
        }

        /**
         * @var Response $response
         */
        $response = Context::get(Response::class);
        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);

        if (($response instanceof Response)) {
            if (!$request->cookie(Session::NAME)) {
                $value = Str::generateRandStr(32);
                $response->withCookie(Session::NAME, $value, 86400 * 365);
                \Closure::bind(function () use ($value, $request) {
                    $request->cookie[Session::NAME] = $value;
                }, null, $request)();
                Context::set(Response::class, $response);
                Context::set(Request::class, $request);
            }

            if (!$request->cookie("client_id")) {
                $value = Str::generateRandStr(32);
                $response->withCookie("client_id", $value, 86400 * 365);
                \Closure::bind(function () use ($value, $request) {
                    $request->cookie["client_id"] = $value;
                }, null, $request)();
                Context::set(Response::class, $response);
                Context::set(Request::class, $request);
            }
        }
    }


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function gc(): void
    {
        $session = Di::instance()->get(Session::class);
        $gc = $session->gc();
        if (!$gc) {
            return;
        }

        Timer::tick(1800000, function () use ($session) {
            $session->gc();
        });
    }
}