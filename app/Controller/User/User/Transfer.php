<?php
declare (strict_types=1);

namespace App\Controller\User\User;


use App\Controller\User\Base;
use App\Interceptor\Identity;
use App\Interceptor\User;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: [User::class, Identity::class])]
class Transfer extends Base
{

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->theme(Theme::USER_TRANSFER, "User/Transfer.html", "转账");
    }
}