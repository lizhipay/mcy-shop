<?php
declare(strict_types=1);

namespace App\Interceptor;


use Kernel\Annotation\Inject;
use Kernel\Annotation\Interface\Interceptor;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Session\Session;
use Kernel\Waf\Firewall;

class Waf implements Interceptor
{
    #[Inject]
    private Session $session;


    /**
     * @param Request $request
     * @param Response $response
     * @param int $type
     * @return Response
     * @throws JSONException
     */
    public function handle(Request $request, Response $response, int $type): Response
    {
        Firewall::instance()->check(function ($message) {
            throw new JSONException("The current session is not secure. Please refresh the web page and try again.");
        });
        return $response;
    }
}