<?php
declare(strict_types=1);

namespace App\Controller\User\Sales;

use App\Controller\User\Base;
use Kernel\Context\Interface\Response;

/**
 * 前台销售登记页面（免登录）
 */
class Register extends Base
{
    /**
     * 销售登记表单页面
     * @return Response
     */
    public function index(): Response
    {
        return $this->response->render("User/Sales/Register.html", "销售登记", [], BASE_PATH . "/app/View/");
    }
}
