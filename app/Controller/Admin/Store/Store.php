<?php
declare (strict_types=1);

namespace App\Controller\Admin\Store;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class Store extends Base
{
    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function index(): Response
    {
        return $this->render("Store/Store.html", "应用商店");
    }

    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function developer(): Response
    {
        return $this->render("Store/Developer.html", "开发者中心");
    }


    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function trade(): Response
    {
        return $this->render("Store/Trade.html", "盈利中心");
    }
}