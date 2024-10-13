<?php
declare(strict_types=1);

namespace App\Interceptor;

use Kernel\Annotation\Inject;
use Kernel\Annotation\Interface\Interceptor;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Language\Language;
use Kernel\Util\Context;

class Plugin implements Interceptor
{

    use \Kernel\Component\Plugin;

    #[Inject]
    private Admin $admin;

    #[Inject]
    private User $user;

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function handle(Request $request, Response $response, int $type): Response
    {
        $plugin = $this->getPlugin();
        if ($plugin->uid == "*") {
            return $this->admin->handle($request, $response, $type);
        } else {
            $response = $this->user->handle($request, $response, $type);
            $user = Context::get(\App\Model\User::class);

            if ($user?->id != $plugin->uid) {
                return $this->notPermission($request, $response, $type);
            }

            return $response;
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     * @throws \ReflectionException
     */
    private function notPermission(Request $request, Response $response, int $type): Response
    {
        if ($type == \Kernel\Annotation\Interceptor::API) {
            return $response->end()->json(0, Language::instance()->output("你没有权限"));
        } else {
            return $response->end()->render(
                template: "302.html",
                data: ["url" => "/user/trade/order", "time" => 1, "message" => Language::instance()->output("你没有权限")]
            );
        }
    }
}