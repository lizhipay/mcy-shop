<?php
declare (strict_types=1);

namespace App\Controller\Admin\Manage;

use App\Const\Cookie;
use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class Personal extends Base
{
    /**
     * @return Response
     */
    public function logout(): Response
    {
        \App\Model\Manage::query()->where("id", $this->getManage()->id)->update(["login_status" => 0]);
        return $this->response->withCookie(Cookie::MANAGE_TOKEN, "", 0)->redirect("/admin");
    }


    /**
     * @return Response
     */
    public function loginLog(): Response
    {
        return $this->render("Personal/LoginLog.html", "登录日志");
    }
}