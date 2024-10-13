<?php
declare(strict_types=1);

namespace App\Controller\User\Repertory;

use App\Controller\User\Base;
use App\Interceptor\Supplier;
use App\Interceptor\User;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Theme;

#[Interceptor(class: [User::class, Supplier::class])]
class ItemMarkupTemplate extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->theme(Theme::USER_REPERTORY_ITEM_MARKUP_TEMPLATE, "Repertory/ItemMarkupTemplate.html", "同步模板");
    }
}