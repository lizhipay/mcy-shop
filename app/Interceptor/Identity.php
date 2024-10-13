<?php
declare (strict_types=1);

namespace App\Interceptor;

use App\Model\User;
use App\Model\UserIdentity;
use Kernel\Annotation\Interface\Interceptor;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Language\Language;
use Kernel\Util\Context;

class Identity implements Interceptor
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
        $identity = UserIdentity::query()->where("user_id", $user->id)->first();

        if (!$identity || $identity->status != 1) {
            return $this->notIdentity($request, $response, $type);
        }
        
        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     */
    private function notIdentity(Request $request, Response $response, int $type): Response
    {
        if ($type == \Kernel\Annotation\Interceptor::API) {
            return $response->end()->json(0, Language::instance()->output("请先实名认证"));
        } else {
            return $response->end()->render(
                template: "302.html",
                data: ["url" => "/user/security#user-identity-tab", "time" => 1, "message" => Language::instance()->output("请先实名认证")]
            );
        }
    }
}