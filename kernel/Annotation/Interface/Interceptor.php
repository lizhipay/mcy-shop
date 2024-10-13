<?php
declare (strict_types=1);

namespace Kernel\Annotation\Interface;


use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;


interface Interceptor
{
    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     */
    public function handle(Request $request, Response $response, int $type): Response;
}