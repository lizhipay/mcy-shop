<?php
declare (strict_types=1);

namespace App\Controller\User\User;

use App\Controller\User\Base;
use App\Interceptor\User;
use App\Model\UserIdentity;
use App\Service\Common\Config;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: User::class)]
class Security extends Base
{

    #[Inject]
    private Config $config;

    /**
     * @return Response
     */
    public function index(): Response
    {
        $userIdentity = UserIdentity::query()->where("user_id", $this->getUser()->id)->first();
        $config = $this->config->getMainConfig("register");
        return $this->theme(Theme::USER_SECURITY, "User/Security.html", "安全中心", ["userIdentity" => $userIdentity, "option" => $config]);
    }


    /**
     * @return Response
     */
    public function loginLog(): Response
    {
        return $this->theme(Theme::USER_LOGIN_LOG, "User/LoginLog.html", "登录日志");

    }
}