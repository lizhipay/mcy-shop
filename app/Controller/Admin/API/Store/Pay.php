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
class Pay extends Base
{

    #[Inject]
    private \App\Service\Store\Pay $pay;

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    public function getList(): Response
    {
        $data = $this->pay->getList($this->getStoreAuth(), $this->request->post("equipment") == 1 ? 1 : 2);
        return $this->json(data: $data['list'], ext: ["balance" => $data['balance']]);
    }
}