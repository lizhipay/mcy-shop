<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Store;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Identity extends Base
{
    #[Inject]
    private \App\Service\Store\Identity $identity;


    /**
     * @param string $tradeNo
     * @return Response
     * @throws RuntimeException
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Store\Identity::class, "tradeNo"]
    ])]
    public function status(string $tradeNo): Response
    {
        return $this->json(data: $this->identity->status($this->getStoreAuth(), $tradeNo));
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Store\Identity::class, ["certName", "certNo"]]
    ])]
    public function certification(): Response
    {
        $certName = $this->request->post("cert_name");
        $certNo = $this->request->post("cert_no");
        return $this->json(data: ["url" => $this->identity->certification($certName, $certNo, $this->getStoreAuth())]);
    }
}