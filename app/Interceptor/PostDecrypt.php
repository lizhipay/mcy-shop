<?php
declare (strict_types=1);

namespace App\Interceptor;

use Kernel\Annotation\Inject;
use Kernel\Annotation\Interface\Interceptor;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Session\Session;
use Kernel\Util\Aes;
use Kernel\Util\Context;
use Kernel\Util\Str;
use Kernel\Waf\Firewall;

class PostDecrypt implements Interceptor
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
        $secret = (string)$request->header("Secret");
        $signature = (string)$request->header("Signature");

        $data = $request->raw();

        if (!$data) {
            return $response;
        }

        $key = substr($secret, 0, 16);
        $post = (array)json_decode((string)Aes::decrypt($data, $key, $key), true);


        if (!$signature || Str::generateSignature($post, $secret) != $signature) {
            throw new JSONException("signature failure");
        }

        $request->setProperty("post", Firewall::instance()->xssKiller($post));
        Context::set(Request::class, $request);

        return $response;
    }
}