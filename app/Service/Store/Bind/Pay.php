<?php
declare (strict_types=1);

namespace App\Service\Store\Bind;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Inject;
use Kernel\Exception\ServiceException;

class Pay implements \App\Service\Store\Pay
{

    #[Inject]
    private \App\Service\Store\Http $http;


    /**
     * @param Authentication $authentication
     * @param int $equipment
     * @return array
     * @throws ServiceException
     */
    public function getList(Authentication $authentication, int $equipment = 1): array
    {
        $http = $this->http->request("/pay/list", [
            "equipment" => $equipment
        ], $authentication);

        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "获取支付列表失败");
        }
        return ["list" => $http->data, "balance" => $http->origin['balance']];
    }

    /**
     * @param Authentication $authentication
     * @param string $tradeNo
     * @return array
     * @throws ServiceException
     */
    public function getPayOrder(Authentication $authentication, string $tradeNo): array
    {
        $http = $this->http->request("/pay/order", [
            "trade_no" => $tradeNo
        ], $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message ?? "获取支付订单状态失败");
        }
        return $http->data;
    }
}