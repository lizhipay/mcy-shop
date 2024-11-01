<?php
declare(strict_types=1);

namespace App\Controller\Admin\Shop;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class ItemMarkupTemplate extends Base
{
    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function index(): Response
    {
        return $this->render("Shop/ItemMarkupTemplate.html", "同步模板");
    }
}