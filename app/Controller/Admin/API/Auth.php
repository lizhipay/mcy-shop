<?php
declare (strict_types=1);

namespace App\Controller\Admin\API;

use App\Controller\Admin\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Waf;
use App\Service\Admin\Manage;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\App;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Const\Plugin as PGI;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;


#[Interceptor(class: [PostDecrypt::class, Waf::class], type: Interceptor::API)]
class Auth extends Base
{

    #[Inject]
    private Manage $manage;

    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function login(): Response
    {
        $hook = Plugin::instance()->hook(App::$mEnv, Point::ADMIN_API_AUTH_LOGIN_BEFORE, PGI::HOOK_TYPE_HTTP, $this->request, $this->response);
        if ($hook instanceof Response) return $hook;

        return $this->manage->login($this->request, $this->response);
    }
}