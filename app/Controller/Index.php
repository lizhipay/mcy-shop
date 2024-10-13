<?php
declare(strict_types=1);

namespace App\Controller;

use Kernel\Annotation\Inject;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Sync;
use Kernel\Plugin\Usr;

class Index
{

    #[Inject]
    protected Request $request;

    #[Inject]
    protected Response $response;

    /**
     * @return Response
     */
    public function hello(): Response
    {
        return $this->response->json();
    }


    /**
     * @return Response
     * @throws \ReflectionException
     */
    public function wait(): Response
    {
        $list = Sync::inst()->list();
        return $this->response->json(data: ["state" => empty($list)]);
    }


    /**
     * @return Response
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function owner(): Response
    {
        $usr = Usr::inst()->getUsr();
        return $this->response->json(data: ["usr" => $usr]);
    }
}