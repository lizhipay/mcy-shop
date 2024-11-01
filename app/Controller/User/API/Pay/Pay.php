<?php
declare (strict_types=1);

namespace App\Controller\User\API\Pay;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Visitor;
use App\Interceptor\Waf;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\UserAgent;

#[Interceptor(class: [PostDecrypt::class, Waf::class, Visitor::class], type: Interceptor::API)]
class Pay extends Base
{

    #[Inject]
    private \App\Service\User\Pay $pay;


    /**
     * @return Response
     * @throws RuntimeException
     * @throws NotFoundException
     */
    public function getList(): Response
    {
        $business = (string)$this->request->post("business");
        $amount = (string)$this->request->post("amount");
        $methods = $this->pay->getList(equipment: UserAgent::getEquipment($this->request->header("UserAgent")), business: $business, user: $this->getSiteOwner(), amount: $amount, options: $this->request->post());
        return $this->json(data: $methods, ext: ["balance" => $this->getUser()?->balance, "is_login" => (bool)$this->getUser()]);
    }
}