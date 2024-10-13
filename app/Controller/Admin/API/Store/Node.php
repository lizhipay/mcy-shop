<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Store;

use App\Controller\Admin\Base;
use App\Interceptor\Admin\Online;
use App\Interceptor\PostDecrypt;
use App\Service\Store\Http;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Online::class], type: Interceptor::API)]
class Node extends Base
{
    #[Inject]
    private Http $http;


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function ping(): Response
    {
        return $this->json(data: $this->http->ping(), ext: ["index" => $this->http->getNode()]);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function save(): Response
    {
        $index = $this->request->post("index") ?: 0;
        if ($index > 2 || $index < 0) {
            $index = 0;
        }
        $this->http->setNode($index);
        return $this->json();
    }
}