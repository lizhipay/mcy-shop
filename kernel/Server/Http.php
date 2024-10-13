<?php
declare (strict_types=1);

namespace Kernel\Server;

use Kernel\Annotation\Collector;
use Kernel\Component\Singleton;
use Kernel\Constant\Exception;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\NotFoundException;
use Kernel\Language\Entity\Language;
use Kernel\Plugin\Const\Plugin as PGC;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Context;
use Kernel\Util\Route;

class Http
{

    use Singleton;


    /**
     * @param Request $request
     * @return mixed
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function call(Request $request): mixed
    {
        //首选项语言上下文
        Context::set(Language::class, \Kernel\Language\Language::instance()->getPreferredLanguage($request));
        if (($hook = Plugin::instance()->hook(Usr::MAIN, Point::HTTP_REQUEST_ENTER, PGC::HOOK_TYPE_HTTP, $request, Context::get(Response::class))) instanceof Response) return $hook;
        $_env = App::env();
        if (($hook = Plugin::instance()->hook($_env, Point::HTTP_REQUEST_START, PGC::HOOK_TYPE_HTTP, $request, Context::get(Response::class))) instanceof Response) return $hook;
        $uri = $request->uri();

        //$urls = $this->getUri($uri);
        $method = $request->method();
        $route = $uri;

        if (!Route::has($uri, $method)) { //  试运行，删除路由判断：&& !Route::has($route = $urls['first'], $method)
            if (($hook = Plugin::instance()->hook($_env, Point::HTTP_NOT_FOUND, PGC::HOOK_TYPE_HTTP, $request, Context::get(Response::class))) instanceof Response) return $hook;
            throw new NotFoundException(Exception::NOT_FOUND);
        }


        $router = clone(Route::get($route, $method));
        $namespace = $router->class();
        $action = $router->action();  //试运行，删除action的参数：$urls['end']
        $router->setAction($action);
        $router->setRoute($uri);

        //路由上下文
        Context::set(\Kernel\Context\Interface\Route::class, $router);

        //检测类是否存在
        if (!class_exists($namespace)) {
            if (($hook = Plugin::instance()->hook($_env, Point::HTTP_NOT_FOUND, PGC::HOOK_TYPE_HTTP, $request, Context::get(Response::class))) instanceof Response) return $hook;
            throw new NotFoundException(Exception::NOT_FOUND);
        }

        $controller = new $namespace;

        //检测method是否存在
        if (!method_exists($controller, $action)) {
            if (($hook = Plugin::instance()->hook($_env, Point::HTTP_NOT_FOUND, PGC::HOOK_TYPE_HTTP, $request, Context::get(Response::class))) instanceof Response) return $hook;
            throw new NotFoundException(Exception::NOT_FOUND);
        }


        Collector::instance()->classParse($controller, function (\ReflectionAttribute $attribute) {
            $attribute->newInstance();
        });

        Collector::instance()->methodParse($controller, $action, function (\ReflectionAttribute $attribute) {
            $attribute->newInstance();
        });

        /**
         * @var Response $response
         */
        $response = Context::get(Response::class);
        $forcedEnd = $response->getOptions("forced_end");
        if ($forcedEnd) {
            return $response;
        }

        //依赖绑定
        Di::instance()->inject($controller);
        //获取参数
        $parameters = Collector::instance()->getMethodParameters($controller, $action, $request->get());

        if (($hook = Plugin::instance()->hook($_env, Point::HTTP_REQUEST_CONTROLLER, PGC::HOOK_TYPE_HTTP, $router, $request, Context::get(Response::class))) instanceof Response) return $hook;
        return call_user_func_array([$controller, $action], $parameters);
    }
}