<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Store;


use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
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