<?php
declare (strict_types=1);

namespace App\Controller\User\Plugin;

use App\Controller\User\Base;
use App\Interceptor\Group;
use App\Interceptor\User;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\ViewException;
use Kernel\Plugin\Const\Theme;
use Kernel\Plugin\Usr;

#[Interceptor(class: [User::class, Group::class])]
class Plugin extends Base
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->theme(Theme::USER_PLUGIN, "Plugin/Plugin.html", "插件管理");
    }

    /**
     * @param string $name
     * @return Response
     * @throws ViewException
     */
    public function wiki(string $name): Response
    {
        $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($name, Usr::inst()->userToEnv($this->getUser()->id));
        if (!$plugin) {
            throw new ViewException("插件不存在");
        }
        return $this->theme(Theme::USER_PLUGIN_WIKI, "Plugin/Wiki.html", "插件文档", ["plugin" => $plugin, "requestUrl" => $this->request->url()]);
    }
}