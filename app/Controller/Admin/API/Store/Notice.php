<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Store;

use App\Controller\Admin\Base;
use App\Interceptor\Admin\Online;
use App\Interceptor\PostDecrypt;
use App\Service\Store\Project;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;


#[Interceptor(class: [PostDecrypt::class, Online::class], type: Interceptor::API)]
class Notice extends Base
{
    #[Inject]
    private Project $project;

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function list(): Response
    {
        return $this->json(data: $this->project->getNotice());
    }
}