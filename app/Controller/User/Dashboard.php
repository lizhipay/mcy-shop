<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Interceptor\User;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: User::class)]
class Dashboard extends Base
{

    public function index(): Response
    {
        return $this->theme(Theme::DASHBOARD, "Dashboard.html", "控制台");
    }
}