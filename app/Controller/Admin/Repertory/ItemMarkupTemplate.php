<?php
declare(strict_types=1);

namespace App\Controller\Admin\Repertory;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;

#[Interceptor(class: Admin::class)]
class ItemMarkupTemplate extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render("Repertory/ItemMarkupTemplate.html", "同步模板");
    }
}