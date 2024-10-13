<?php
declare (strict_types=1);

namespace App\Controller\User\User;

use App\Controller\User\Base;
use App\Interceptor\Merchant;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: [\App\Interceptor\User::class, Merchant::class])]
class User extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->theme(Theme::USER, "User/User.html", "会员管理");
    }
}