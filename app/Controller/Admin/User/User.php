<?php
declare (strict_types=1);

namespace App\Controller\Admin\User;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class User extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render("User/User.html", "会员管理");
    }


    /**
     * @return Response
     */
    public function bill(): Response
    {
        return $this->render("User/Bill.html", "账单记录");
    }


    /**
     * @return Response
     */
    public function level(): Response
    {
        return $this->render("User/Level.html", "会员等级");
    }

    /**
     * @return Response
     */
    public function group(): Response
    {
        return $this->render("User/Group.html", "商家权限");
    }


    /**
     * @return Response
     */
    public function identity(): Response
    {
        return $this->render("User/Identity.html", "实名管理");
    }

    /**
     * @return Response
     */
    public function withdraw(): Response
    {
        return $this->render("User/Withdraw.html", "提现管理");
    }

    /**
     * @return Response
     */
    public function site(): Response
    {
        return $this->render("User/Site.html", "站点管理");
    }
}