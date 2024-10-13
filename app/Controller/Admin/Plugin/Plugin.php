<?php
declare (strict_types=1);

namespace App\Controller\Admin\Plugin;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\App;
use Kernel\Context\Interface\Response;
use Kernel\Exception\ViewException;

#[Interceptor(class: Admin::class)]
class Plugin extends Base
{
    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function index(): Response
    {
        return $this->render("Plugin/Plugin.html", "插件管理");
    }


    /**
     * @param string $name
     * @return Response
     * @throws ViewException
     * @throws \ReflectionException
     */
    public function wiki(string $name): Response
    {
        $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($name, App::$mEnv);
        if (!$plugin) {
            throw new ViewException("插件不存在");
        }
        return $this->render("Plugin/Wiki.html", "插件文档", ["plugin" => $plugin, "requestUrl" => $this->request->url()]);
    }
}