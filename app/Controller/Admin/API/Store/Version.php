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
class Version extends Base
{
    #[Inject]
    private Project $project;

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function latest(): Response
    {
        return $this->json(data: $this->project->getVersionLatest());
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function list(): Response
    {
        return $this->json(data: $this->project->getVersionList());
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function update(): Response
    {
        $this->project->update();
        return $this->json();
    }


    /**
     * @param string $hash
     * @return Response
     * @throws RuntimeException
     */
    public function getUpdateLog(string $hash): Response
    {
        return $this->json(data: $this->project->getUpdateLog($hash)->toArray());
    }
}