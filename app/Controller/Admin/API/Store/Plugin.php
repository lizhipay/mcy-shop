<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Store;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Service\Store\Http;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;


#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Plugin extends Base
{


    #[Inject]
    private \App\Service\Store\Plugin $plugin;

    #[Inject]
    private Http $http;


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function save(): Response
    {
        $data = $this->request->post();
        //上传文件
        $http = $this->http->upload("image", BASE_PATH . $data['icon'], $this->getStoreAuth());
        $data['icon'] = $http->data['url'];
        $this->plugin->createOrUpdate($data, $this->getStoreAuth());
        return $this->json();
    }
}