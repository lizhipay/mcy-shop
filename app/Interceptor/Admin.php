<?php
declare(strict_types=1);

namespace App\Interceptor;

use App\Const\Cookie;
use App\Model\Manage;
use App\Model\Permission;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interface\Interceptor;
use Kernel\Context\App;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Language\Language;
use Kernel\Plugin\Const\Plugin as PGI;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Util\Context;
use Kernel\Util\Date;

class Admin implements Interceptor
{
    #[Inject]
    private \App\Service\Admin\Manage $manage;

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function handle(Request $request, Response $response, int $type): Response
    {

        $manageToken = base64_decode((string)$request->cookie(Cookie::MANAGE_TOKEN));
        $head = \Kernel\Util\JWT::inst()->getHead($manageToken);

        if (!isset($head['mid'])) {
            return $this->login($request, $response, $type);
        }

        $manage = Manage::find($head['mid']);

        if (!$manage) {
            return $this->login($request, $response, $type);
        }

        try {
            $jwt = JWT::decode($manageToken, new Key($manage->password, 'HS256'));
        } catch (\Exception $e) {
            return $this->login($request, $response, $type);
        }

        if (
            $jwt->expire <= time() ||
            $manage->login_time != $jwt->loginTime ||
            $manage->login_ip != $request->clientIp() ||
            $manage->login_status != 1 ||
            $manage->status != 1
        ) {
            return $this->login($request, $response, $type);
        }

        $menu = $this->manage->getMenu($manage);
        $router = trim($request->uri() . "@" . $request->method(), "/");

        if (!in_array($router, $menu['route']) && Permission::isRegister("/" . $router)) {
            return $this->notPermission($request, $response, $type);
        }

        $hook = Plugin::instance()->hook(App::env(), Point::ADMIN_INTERCEPTOR_SESSION_ONLINE, PGI::HOOK_TYPE_HTTP, $request, $response, $type, $manage);
        if ($hook instanceof Response) return $hook;


        if (!file_exists(BASE_PATH . "/config/terms")) {
            if ($router === "admin/dashboard@GET" && $request->get("agree") == 1) {
                file_put_contents(BASE_PATH . "/config/terms", "用户同意协议，时间：" . Date::current());
                return $response->end()->json(200, "success");
            }
            return $response->end()->render("LegalTerms.html");
        }

        Context::set(Manage::class, $manage);
        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    private function login(Request $request, Response $response, int $type): Response
    {
        $response->withCookie(Cookie::MANAGE_TOKEN, "", 0);

        $hook = Plugin::instance()->hook(App::env(), Point::ADMIN_INTERCEPTOR_SESSION_OFFLINE, PGI::HOOK_TYPE_HTTP, $request, $response, $type);
        if ($hook instanceof Response) return $hook;

        if ($type == \Kernel\Annotation\Interceptor::API) {
            return $response->end()->json(0, "登录已过期");
        } else {
            $a = http_build_query($request->get());
            return $response->end()->render(
                template: "302.html",
                data: ["url" => "/admin?goto=" . urlencode($request->uri() . ($a ? "?" . $a : "")), "time" => 1, "message" => Language::instance()->output("登录已过期")]
            );
        }
    }


    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    private function notPermission(Request $request, Response $response, int $type): Response
    {
        $hook = Plugin::instance()->hook(App::env(), Point::ADMIN_INTERCEPTOR_NOT_PERMISSION, PGI::HOOK_TYPE_HTTP, $request, $response, $type);
        if ($hook instanceof Response) return $hook;

        if ($type == \Kernel\Annotation\Interceptor::API) {
            return $response->end()->json(0, "您没有权限访问");
        } else {
            return $response->end()->render(
                template: "302.html",
                data: ["url" => "/admin/dashboard", "time" => 1, "message" => Language::instance()->output("您没有权限访问")]
            );
        }
    }
}