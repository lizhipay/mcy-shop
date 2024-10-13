<?php
declare(strict_types=1);

namespace App\Controller\Admin\Main;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Model\RepertoryOrder;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class Dashboard extends Base
{

    public function index(): Response
    {





        return $this->render("Main/Dashboard.html", "控制台");
    }
}