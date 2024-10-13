<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Controller\User\Base;
use App\Interceptor\Identity;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Service\User\Balance;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Identity::class], type: Interceptor::API)]
class Transfer extends Base
{

    #[Inject]
    private Balance $balance;

    /**
     * @return Response
     * @throws RuntimeException
     * @throws \Throwable
     */
    #[Validator([
        [\App\Validator\User\Transfer::class, ['payee', 'amount']]
    ])]
    public function to(): Response
    {
        $to = \App\Model\User::query()->where("username", $this->request->post("payee"))->first();
        if (!$to || $to->id == $this->getUser()->id){
            throw new JSONException("收款人未找到");
        }
        Db::transaction(function () use ($to) {
            $this->balance->transfer($this->getUser()->id, $to->id, (string)$this->request->post('amount'));
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);

        return $this->json(data: ["balance" => \App\Model\User::find($this->getUser()->id)?->balance]);
    }
}