<?php /** @noinspection PhpUnhandledExceptionInspection */
declare (strict_types=1);

use Kernel\Context\FPM\Request;
use Kernel\Context\FPM\Response;
use Kernel\Context\Interface\Request as RequestInterface;
use Kernel\Context\Interface\Response as ResponseInterface;
use Kernel\Server\Http;
use Kernel\Session\Manager;
use Kernel\Util\Context;

try {
    $req = new  Request();
    $resp = new Response();
    //context
    Context::set(RequestInterface::class, $req);
    Context::set(ResponseInterface::class, $resp);
    Manager::instance()->create();

    //运行HTTP服务
    $resp = Http::instance()->call($req);

    if ($resp instanceof Response) {
        $resp->draw();
        return;
    }

    throw new \Kernel\Exception\RuntimeException("controller return value should be：" . Response::class);
} catch (Throwable $e) {
    $response = Context::get(ResponseInterface::class);
    echo \Kernel\Context\App::error($e, $response);
    exit;
}