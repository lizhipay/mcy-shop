<?php
declare(strict_types=1);

namespace App\Controller\Admin\Manage;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class Role extends Base
{

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render("Manage/Role.html", "角色管理");
    }
}