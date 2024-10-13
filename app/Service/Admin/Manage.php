<?php
declare(strict_types=1);

namespace App\Service\Admin;

use Kernel\Annotation\Bind;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;

#[Bind(class: \App\Service\Admin\Bind\Manage::class)]
interface Manage
{
    /**
     * 管理员登录
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function login(Request $request, Response $response): Response;


    /**
     * @param \App\Model\Manage $manage
     * @return array
     */
    public function getMenu(\App\Model\Manage $manage): array;
}