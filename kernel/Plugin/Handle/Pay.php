<?php
declare (strict_types=1);

namespace Kernel\Plugin\Handle;

use Kernel\Context\Interface\Response;

interface Pay
{

    /**
     * 创建订单
     * @return \Kernel\Plugin\Entity\Pay
     */
    public function create(): \Kernel\Plugin\Entity\Pay;


    /**
     * 异步通知
     * @return Response
     */
    public function async(): Response;
}