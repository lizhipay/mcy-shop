<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\User;


use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\UserWithdraw as Model;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Withdraw extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\Withdraw $withdraw;


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(): Response
    {
        $map = $this->request->post();
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setOrderBy("id", "desc");
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $raw = [];
        $data = $this->query->get($get, function (Builder $builder) use ($map, &$raw) {
            $raw['not_processed'] = (clone $builder)->where("status", 0)->count();
            $raw['processed'] = (clone $builder)->where("status", 1)->count();
            $raw['reject_processed'] = (clone $builder)->where("status", 2)->count();
            $raw['withdraw_amount'] = (clone $builder)->where("status", 1)->sum("amount");
            return $builder->with([
                'card',
                'user' => function (HasOne $one) {
                    $one->with("identity")->select(['id', 'username', 'avatar']);

                }
            ]);
        });
        return $this->json(data: $data, ext: $raw);
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Admin\Withdraw::class, ["id", "status", "lockCard"]]
    ])]
    public function processed(): Response
    {
        $this->withdraw->processed(
            withdrawId: $this->request->post("id", Filter::INTEGER),
            lockCard: $this->request->post("lock_card", Filter::BOOLEAN),
            status: $this->request->post("status", Filter::INTEGER),
            message: $this->request->post("message") ?? ""
        );
        return $this->json();
    }
}