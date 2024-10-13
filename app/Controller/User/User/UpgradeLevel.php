<?php
declare (strict_types=1);

namespace App\Controller\User\User;

use App\Controller\User\Base;
use App\Interceptor\User;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: User::class)]
class UpgradeLevel extends Base
{

    #[Inject]
    private \App\Service\User\Level $level;

    /**
     * @return Response
     */
    public function index(): Response
    {
        $level = $this->level->getList($this->getUser());
        return $this->theme(Theme::USER_SELF_LEVEL, "User/UpgradeLevel.html", "升级会员等级", ["level" => $level]);
    }
}