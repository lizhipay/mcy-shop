<?php
declare (strict_types=1);

namespace App\Controller\User\User;

use App\Const\Cookie;
use App\Controller\User\Base;
use App\Interceptor\User;
use App\Service\User\Lifetime;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: User::class)]
class Personal extends Base
{

    #[Inject]
    private Lifetime $lifetime;

    /**
     * @return Response
     */
    public function index(): Response
    {
        $lifetime = $this->lifetime->get($this->getUser()->id);

        return $this->theme(Theme::USER_PERSONAL, "User/Personal.html", "我的资料", ["lifetime" => $lifetime]);
    }


    /**
     * @return Response
     */
    public function logout(): Response
    {
        $this->lifetime->update($this->getUser()->id, "login_status", 0);
        return $this->response->withCookie(Cookie::USER_TOKEN, "", 0)->redirect("/");
    }
}