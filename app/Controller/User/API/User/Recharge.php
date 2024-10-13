<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Service\User\Order;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class Recharge extends Base
{

    #[Inject]
    private Order $order;


    /**
     * @return Response
     * @throws RuntimeException
     */

    #[Validator([
        [\App\Validator\User\Recharge::class, "amount"]
    ])]
    public function trade(): Response
    {
        $amount = $this->request->post("amount");
        $trade = $this->order->recharge(
            amount: $amount,
            clientId: (string)$this->request->cookie("client_id"),
            createIp: $this->request->clientIp(),
            createUa: $this->request->header("UserAgent"),
            customer: $this->getUser()
        );
        return $this->json(data: $trade->toArray());
    }
}