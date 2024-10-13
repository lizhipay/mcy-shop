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
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class OpenMerchant extends Base
{

    #[Inject]
    private \App\Service\User\OpenMerchant $openMerchant;


    /**
     * @return Response
     * @throws RuntimeException
     */

    #[Validator([
        [\App\Validator\User\OpenMerchant::class, "groupId"]
    ])]
    public function trade(): Response
    {
        $groupId = $this->request->post("group_id", Filter::INTEGER);


        $trade = $this->openMerchant->trade(
            user: $this->getUser(),
            groupId: $groupId,
            clientId: (string)$this->request->cookie("client_id"),
            userAgent: $this->request->header("UserAgent"),
            clientIp: $this->request->clientIp()
        );

        return $this->json(data: $trade->toArray());
    }

}