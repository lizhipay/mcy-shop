<?php
declare (strict_types=1);

namespace App\Controller\Admin;

use App\Const\Cookie;
use Kernel\Context\Interface\Response;

class Auth extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->login();
    }

    /**
     * @return Response
     */
    public function login(): Response
    {

        $cookie = $this->request->cookie();

        if (isset($cookie[Cookie::MANAGE_TOKEN])) {
            return $this->response->redirect("/admin/dashboard");
        }

        return $this->render("Auth/Login.html", "登录");
    }
}