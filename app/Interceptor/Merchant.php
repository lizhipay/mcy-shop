<?php
declare (strict_types=1);

namespace App\Interceptor;

use App\Model\User;
use App\Model\UserGroup;
use Kernel\Annotation\Interface\Interceptor;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Language\Language;
use Kernel\Util\Context;

class Merchant implements Interceptor
{

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     */
    public function handle(Request $request, Response $response, int $type): Response
    {

        /**
         * @var User $user
         */
        $user = Context::get(User::class);
        if (!$user) {
            return $this->notPermission($request, $response, $type);
        }
        /**
         * @var UserGroup $group
         */
        $group = $user->group;

        if (!$group) {
            return $this->notPermission($request, $response, $type);
        }

        if ($group->is_merchant != 1) {
            return $this->notPermission($request, $response, $type);
        }
        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
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