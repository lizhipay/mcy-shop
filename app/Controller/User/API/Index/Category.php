<?php
declare (strict_types=1);

namespace App\Controller\User\API\Index;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Waf;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException as RuntimeExceptionAlias;


#[Interceptor(class: [PostDecrypt::class, Waf::class], type: Interceptor::API)]
class Category extends Base
{
    #[Inject]
    private \App\Service\User\Category $category;

    /**
     * @return Response
     * @throws RuntimeExceptionAlias
     */
    public function available(): Response
    {
        $category = $this->category->only($this->getSiteOwner());
        return $this->json(200, "success", $category);
    }
}