<?php
declare (strict_types=1);

namespace App\Controller\User\API;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Service\User\Order;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Call;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
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

        $userId = $this->getUser()->id;
        Call::create(function () use ($userId) {
            $this->order->autoReceipt($userId);
        });
        return $this->json();
    }
}