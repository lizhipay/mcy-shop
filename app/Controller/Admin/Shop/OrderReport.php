<?php
declare (strict_types=1);

namespace App\Controller\Admin\Shop;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class OrderReport extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render("Shop/OrderReport.html", "维权订单");
    }
}