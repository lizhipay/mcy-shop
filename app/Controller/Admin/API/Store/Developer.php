<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Store;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Service\Store\Http;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Usr;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Developer extends Base
{
    #[Inject]
    private Http $http;

    #[Inject]
    private \App\Service\Store\Developer $developer;


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function pluginList(): Response
    {
        return $this->json(data: $this->developer->pluginList($this->request->post(), $this->getStoreAuth()));
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function createOrUpdatePlugin(): Response
    {
        $data = $this->request->post();

        if (!isset($data['id'])) {
            //上传文件
            $http = $this->http->upload("image", BASE_PATH . $data['icon'], $this->getStoreAuth());
            $data['icon'] = $http->data['url'];
        }

        $this->developer->createOrUpdatePlugin($data, $this->getStoreAuth());
        return $this->json();
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function publishPlugin(): Response
    {
        $this->developer->publishPlugin($this->request->post("key"), $this->getStoreAuth());
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function getPluginTrackedFiles(): Response
    {
        $name = $this->request->post("key");
        $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($name, Usr::MAIN);
        if (!$plugin) {
            throw new ServiceException("插件不存在");
        }
        $pluginTrackedFiles = $this->developer->getPluginTrackedFiles($name);

        foreach ($pluginTrackedFiles as &$file) {
            $file = str_replace($plugin->path, "", $file);
        }

        return $this->json(data: ["files" => $pluginTrackedFiles, "version" => $plugin->info['version']]);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function updatePlugin(): Response
    {
        $this->developer->updatePlugin($this->request->get("key"), $this->request->post("update_content", Filter::NORMAL), $this->getStoreAuth());
        return $this->json();
    }


    /**
     * @param int $pluginId
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function getPluginVersionList(int $pluginId): Response
    {
        $data = $this->developer->getPluginVersionList($pluginId, $this->request->post("page", Filter::INTEGER), $this->request->post("limit", Filter::INTEGER), $this->getStoreAuth());
        return $this->json(data: $data);
    }


    /**
     * @param int $pluginId
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function getPluginAuthorizationList(int $pluginId): Response
    {
        $post = $this->request->post();
        $data = $this->developer->getPluginAuthorizationList($pluginId, $post, $this->getStoreAuth());
        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function addPluginAuthorization(): Response
    {
        $this->developer->addPluginAuthorization($this->request->post(), $this->getStoreAuth());
        return $this->json();
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function removePluginAuthorization(): Response
    {
        $id = (int)$this->request->post("id", Filter::INTEGER);
        $this->developer->removePluginAuthorization($id, $this->getStoreAuth());
        return $this->json();
    }
}