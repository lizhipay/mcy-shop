<?php
declare (strict_types=1);

namespace App\Controller\Admin\API;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Service\User\Order;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Call;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Task extends Base
{


    #[Inject]
    private Order $order;


    /**
     * 自动收货TASK
     * @return Response
     * @throws RuntimeException
     */
    public function autoReceipt(): Response
    {
        Call::create(function () {
            $this->order->autoReceipt();
        });
        return $this->json();
    }
}