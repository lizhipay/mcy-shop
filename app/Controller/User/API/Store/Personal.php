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
class Personal extends Base
{
    #[Inject]
    private \App\Service\Store\Personal $personal;

    /**
     * @return Response
     * @throws RuntimeException
     * @throws JSONException
     */
    public function info(): Response
    {
        return $this->json(data: $this->personal->getInfo($this->getStoreAuth()));
    }
}