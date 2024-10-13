<?php
declare (strict_types=1);

namespace App\Controller\User\API\Plugin;

use App\Controller\User\Base;
use App\Interceptor\Group;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Query;
use Kernel\Plugin\Usr;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Group::class], type: Interceptor::API)]
class Plugin extends Base
{

    /**
     * @return string
     * @throws \ReflectionException
     */
    private function getEnv(): string
    {
        return Usr::inst()->userToEnv($this->getUser()->id);
    }

    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function get(): Response
    {
        $state = $this->request->post("state");
        $state = ($state === "" || $state === null) ? -1 : (int)$state;
        $query = new Query($this->getEnv());
        $query->setState($state);
        $query->setKeyword((string)$this->request->post("keyword"));
        $query->setType((int)$this->request->post("equal-type"));
        $query->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        return $this->json(data: $query->list());
    }

    /**
     * @param string $hash
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function getLogs(string $hash): Response
    {
        $name = $this->request->post("name");
        $data = \Kernel\Plugin\Plugin::instance()->getLogs($hash, $name, $this->getEnv())->toArray();
        return $this->json(data: $data);
    }


    /**
     * @param string $name
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    public function icon(string $name): Response
    {
        $path = realpath(BASE_PATH . $this->getEnv() . "/" . $name . "/Icon.ico");

        if (!$path) {
            throw new JSONException("ICON不存在");
        }
        $file = fopen($path, 'rb');
        if (!$file) {
            throw new JSONException("无法读取文件");
        }
        $image = stream_get_contents($file);
        fclose($file);

        return $this->response->raw($image)
            ->withHeader("Content-Type", "image/png")
            ->withHeader("Cache-Control", "public, max-age=31536000")
            ->withHeader("Pragma", "public, max-age=31536000")
            ->withHeader("Expires", gmdate("D, d M Y H:i:s", time() + 31536000) . " GMT")
            ->withHeader("Date", gmdate("D, d M Y H:i:s", time() + 31536000) . " GMT");
    }

    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function clearLog(): Response
    {
        $name = $this->request->post("name");
        \Kernel\Plugin\Plugin::instance()->clearLog($name, $this->getEnv());
        return $this->json();
    }


    /**
     * @param string $name
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function setCfg(string $name): Response
    {
        $post = $this->request->post(flags: Filter::NORMAL);
        \Kernel\Plugin\Plugin::inst()->instantHook($name, $this->getEnv(), Point::APP_SAVE_CFG_BEFORE, $post);
        \Kernel\Plugin\Plugin::instance()->setConfig($name, $this->getEnv(), $post);
        \Kernel\Plugin\Plugin::inst()->instantHook($name, $this->getEnv(), Point::APP_SAVE_CFG_AFTER, $post);
        return $this->json();
    }

    /**
     * @param string $name
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function setSysCfg(string $name): Response
    {
        \Kernel\Plugin\Plugin::instance()->setSystemConfig($name, $this->getEnv(), $this->request->post());
        return $this->json();
    }


    /**
     * 启动插件
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function start(): Response
    {
        $name = $this->request->post("name");
        \Kernel\Plugin\Plugin::instance()->start($name, $this->getEnv());
        return $this->json();
    }

    /**
     * 停止插件
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function stop(): Response
    {
        $name = $this->request->post("name");
        \Kernel\Plugin\Plugin::instance()->stop($name, $this->getEnv());
        return $this->json();
    }


    /**
     * 重启插件
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function restart(): Response
    {
        $name = $this->request->post("name");
        \Kernel\Plugin\Plugin::instance()->stop($name, $this->getEnv());
        \Kernel\Plugin\Plugin::instance()->start($name, $this->getEnv());
        return $this->json();
    }
}