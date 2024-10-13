<?php
declare (strict_types=1);

namespace App\Controller\User\API\Plugin;

use App\Controller\User\Base;
use App\Interceptor\Group;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\File;
use Kernel\Validator\Method;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Group::class], type: Interceptor::API)]
class Submit extends Base
{
    /**
     * @param string $name
     * @param string $js
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Admin\Submit::class, ["name", "js"]]
    ], Method::GET)]
    public function js(string $name, string $js): Response
    {
        $plugin = Plugin::instance()->getPlugin($name, Usr::inst()->userToEnv($this->getUser()->id));

        if (!$plugin) {
            throw new JSONException("插件不存在");
        }

        $path = $plugin->path . "/Config/Js/" . $js . ".js";
        return $this->json(200, "success", ["code" => File::read($path)]);
    }
}