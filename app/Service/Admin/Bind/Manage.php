<?php
declare(strict_types=1);

namespace App\Service\Admin\Bind;

use App\Const\Cookie;
use App\Const\Memory as MEM;
use App\Model\Config;
use App\Model\Manage as ManageModel;
use App\Model\ManageLog;
use Firebase\JWT\JWT;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Container\Memory;
use Kernel\Context\{App, Interface\Request, Interface\Response};
use Kernel\Exception\JSONException;
use Kernel\Plugin\Const\Plugin as PGI;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Util\Context;
use Kernel\Util\Date;
use Kernel\Util\File;
use Kernel\Util\Route;
use Kernel\Util\Str;
use Kernel\Util\Tree;
use Kernel\Waf\Filter;

class Manage implements \App\Service\Admin\Manage
{

    #[Inject]
    private \App\Service\Common\Config $config;

    #[Inject]
    private \App\Service\Admin\LoginLog $loginLog;

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    public function login(Request $request, Response $response): Response
    {
        /**
         * @var ManageModel $manage
         */
        $manage = ManageModel::query()->where("email", $request->post("email"))->first();

        if (!$manage) {
            throw new JSONException("邮箱或密码有误");
        }

        if ($manage->password != Str::generatePassword(trim((string)$request->post("password")), $manage->salt)) {
            throw new JSONException("邮箱或密码有误");
        }

        $config = $this->config->getMainConfig("site");

        $secureTunnel = min($request->post("secure_tunnel", Filter::INTEGER) ?: 0, 8);

        $manage->last_login_time = $manage->login_time;
        $manage->login_time = Date::current();
        $manage->last_login_ip = $manage->login_ip;
        $manage->login_ip = $request->clientIp();
        $manage->last_login_ua = $manage->login_ua;
        $manage->login_ua = $request->header("UserAgent");
        $manage->client_token = $request->post("token");
        $manage->login_status = 1;
        $manage->save();


        $payload = array(
            "expire" => time() + $config['session_expire'],
            "loginTime" => $manage->login_time
        );

        $jwt = base64_encode(JWT::encode(
            payload: $payload,
            key: $manage->password,
            alg: 'HS256',
            head: ["mid" => $manage->id]
        ));

        $response->withCookie(Cookie::MANAGE_TOKEN, $jwt, (int)$config['session_expire']);

        $hook = Plugin::instance()->hook(App::$mEnv, Point::ADMIN_API_AUTH_LOGIN_AFTER, PGI::HOOK_TYPE_HTTP, $request, $response, $manage);
        if ($hook instanceof Response) return $hook;

        $this->loginLog->create($manage->id, $request->clientIp(), $request->header("UserAgent"));
        Context::set(ManageModel::class, $manage);

        File::write(BASE_PATH . "/runtime/secure.tunnel", (string)$secureTunnel);

        return $response->json(200, "success", ["token" => $jwt]);
    }

    /**
     * @param ManageModel $manage
     * @return array
     * @throws \ReflectionException
     */
    public function getMenu(ManageModel $manage): array
    {
        if (Memory::instance()->has(MEM::ADMIN_MANAGE_MENU_ROUTE)) {
            return Memory::instance()->get(MEM::ADMIN_MANAGE_MENU_ROUTE);
        }

        $obj = ManageModel::with(["role" => function (Relation $relation) {
            $relation->with(['permission' => function (Relation $relation) {
                $relation->orderBy("rank", "asc");
            }]);
        }])->find($manage->id, "id");

        $menu = [];
        $route = [];
        $logs = [];

        foreach ($obj->role as $item) {
            foreach ($item->permission as $permission) {
                if (isset($logs[(string)$permission->id])) {
                    continue;
                }
                if ($permission->type == Route::TYPE_MENU || $permission->type == Route::TYPE_PAGE) {
                    $menu[] = [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'icon' => $permission->icon,
                        'pid' => $permission->pid,
                        'type' => $permission->type,
                        'route' => explode("@", (string)$permission->route)[0]
                    ];
                }

                if ($permission->type == Route::TYPE_ROUTE || $permission->type == Route::TYPE_PAGE) {
                    $route[] = trim($permission->route, "/");
                }

                $logs[(string)$permission->id] = true;
            }
        }

        $data = ["menu" => Tree::generate($menu, "id", "pid", "children"), "route" => $route];
        Memory::instance()->set(MEM::ADMIN_MANAGE_MENU_ROUTE, $data);
        return $data;
    }
}