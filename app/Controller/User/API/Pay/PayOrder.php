<?php
declare (strict_types=1);

namespace App\Controller\User\API\Pay;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Visitor;
use App\Interceptor\Waf;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ViewException;
use Kernel\Waf\Filter;

class PayOrder extends Base
{
    #[Inject]
    protected \App\Service\User\PayOrder $payOrder;


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Interceptor(class: [PostDecrypt::class, Waf::class, Visitor::class], type: Interceptor::API)]
    #[Validator([
        [\App\Validator\User\Order::class, "tradeNo"]
    ])]
    public function pay(): Response
    {
        $tradeNo = $this->request->post("trade_no");
        $method = (int)$this->request->post("method");
        $balance = (bool)$this->request->post("balance", Filter::BOOLEAN);
        $pay = $this->payOrder->pay($tradeNo, $method, $balance, $this->request->clientIp(), $this->request->url(), $this->getUser());
        return $this->json(200, "success", $pay->toArray());
    }

    /**
     * @throws RuntimeException
     */
    #[Interceptor(class: [PostDecrypt::class, Waf::class, Visitor::class], type: Interceptor::API)]
    #[Validator([
        [\App\Validator\User\Order::class, "tradeNo"]
    ])]
    public function getPayOrder(): Response
    {
        $tradeNo = $this->request->post("trade_no");
        $order = $this->payOrder->getPayOrder($tradeNo);
        return $this->json(data: $order->toArray());
    }


    /**
     * @return Response
     * @throws ViewException
     */
    public function async(): Response
    {
        $tradeNo = $this->request->uriSuffix();
        if (!$tradeNo) {
            throw new ViewException("请勿随意修改URL");
        }
        return $this->payOrder->async($tradeNo, $this->request->clientIp());
    }

}
