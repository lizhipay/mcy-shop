<?php
declare (strict_types=1);

namespace App\Controller\Admin\User;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class BankCard extends Base
{

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render("User/BankCard.html", "银行卡管理");
    }
}