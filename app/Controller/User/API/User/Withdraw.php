<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Identity;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\UserWithdraw;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Identity::class], type: Interceptor::API)]
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
        $get = new Get(UserWithdraw::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->with(['card'])->where("user_id", $this->getUser()->id);
        });
        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Withdraw::class, ["cardId", "amount"]]
    ])]
    public function apply(): Response
    {
        $amount = $this->request->post("amount");
        $withdraw = $this->_config->getMainConfig("withdraw");

        if ($withdraw['min_withdraw_amount'] != 0 && $amount < $withdraw['min_withdraw_amount']) {
            throw new JSONException("最低兑现金额为：{$withdraw['min_withdraw_amount']}");
        }

        if ($withdraw['max_withdraw_amount'] != 0 && $amount > $withdraw['max_withdraw_amount']) {
            throw new JSONException("最大兑现金额为：{$withdraw['max_withdraw_amount']}");
        }


        $this->withdraw->apply($this->getUser()->id, $this->request->post("card_id", Filter::INTEGER), $this->request->post("amount"));
        return $this->json();
    }
}