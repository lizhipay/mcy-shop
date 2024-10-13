<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Util\UserAgent;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class Pay extends Base
{
    #[Inject]
    private \App\Service\User\Pay $pay;


    /**
     * @return Response
     * @throws RuntimeException
     * 获得支付列表
     */
    public function list(): Response
    {
        $equipment = UserAgent::isMobile((string)$this->request->header("UserAgent")) ? 1 : 2;
        $pay = $this->pay->getList($equipment);
        return $this->json(data: $pay, ext: ["balance" => $this->getUser()->balance]);
    }
}