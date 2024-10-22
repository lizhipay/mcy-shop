<?php
declare (strict_types=1);

namespace App\Controller\User\API\Store;

use App\Controller\User\Base;
use App\Interceptor\Group;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Group::class], type: Interceptor::API)]
class Pay extends Base
{

    #[Inject]
    private \App\Service\Store\Pay $pay;

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function getList(): Response
    {
        $data = $this->pay->getList($this->getStoreAuth(), $this->request->post("equipment") == 1 ? 1 : 2);
        return $this->json(data: $data['list'], ext: ["balance" => $data['balance']]);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function getPayOrder(): Response
    {
        $tradeNo = $this->request->post("trade_no") ?: "";
        return $this->json(data: $this->pay->getPayOrder($this->getStoreAuth(), $tradeNo));
    }
}