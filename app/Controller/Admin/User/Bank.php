<?php
declare (strict_types=1);

namespace App\Controller\Admin\User;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class Bank extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render("User/Bank.html", "银行管理");
    }
}