<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;


use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class UpgradeLevel extends Base
{

    #[Inject]
    private \App\Service\User\Level $level;

    /**
     * @return Response
     * @throws RuntimeException
     */

    #[Validator([
        [\App\Validator\User\UpgradeLevel::class, "levelId"]
    ])]
    public function trade(): Response
    {
        $levelId = $this->request->post("level_id", Filter::INTEGER);

        $trade = $this->level->trade(
            user: $this->getUser(),
            levelId: $levelId,
            clientId: (string)$this->request->cookie("client_id"),
            userAgent: $this->request->header("UserAgent"),
            clientIp: $this->request->clientIp()
        );

        return $this->json(data: $trade->toArray());
    }
}