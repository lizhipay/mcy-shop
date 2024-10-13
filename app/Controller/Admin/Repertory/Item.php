<?php
declare (strict_types=1);

namespace App\Controller\Admin\Repertory;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class Item extends Base
{
    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function index(): Response
    {
        return $this->render("Repertory/Item.html", "货源管理");
    }
}
