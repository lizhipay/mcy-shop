<?php
declare (strict_types=1);

namespace App\Controller\User\User;

use App\Controller\User\Base;
use App\Interceptor\User;
use App\Service\User\Group;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: User::class)]
class OpenMerchant extends Base
{
    #[Inject]
    private Group $group;

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->theme(Theme::USER_OPEN_MERCHANT, "User/OpenMerchant.html", "开通商家", ["group_list" => $this->group->list($this->getUser()->group_id)]);
    }
}