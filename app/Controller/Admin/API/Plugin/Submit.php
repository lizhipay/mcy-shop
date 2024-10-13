<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Plugin;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\File;
use Kernel\Validator\Method;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Submit extends Base
{
    /**
     * @param string $name
     * @param string $js
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\Admin\Submit::class, ["name", "js"]]
    ], Method::GET)]
    public function js(string $name, string $js): Response
    {

        $plugin = Plugin::instance()->getPlugin($name, Usr::MAIN);

        if (!$plugin) {
            throw new JSONException("插件不存在");
        }

        $path = $plugin->path . "/Config/Js/" . $js . ".js";
        return $this->json(200, "success", ["code" => File::read($path)]);
    }
}