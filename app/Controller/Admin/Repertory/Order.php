<?php
declare (strict_types=1);

namespace App\Controller\Admin\Repertory;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;


#[Interceptor(class: Admin::class)]
class Order extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render("Repertory/Order.html", "进货订单");
    }
}