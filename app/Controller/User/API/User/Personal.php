<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class Personal extends Base
{

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function info(): Response
    {
        $user = \App\Model\User::with([
            'lifetime',
            'group' => function (HasOne $one) {
                $one->select(['id', 'name', 'icon']);
            },
            'level' => function (HasOne $one) {
                $one->select(['id', 'name', 'icon']);
            }
        ])->find($this->getUser()->id);

        return $this->json(data: $user->toArray());
    }
}