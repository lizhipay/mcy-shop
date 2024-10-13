<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Waf;
use App\Model\User as Model;
use App\Service\Common\Query;
use App\Service\User\Balance;
use App\Service\User\Lifetime;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Db;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, \App\Interceptor\User::class, Merchant::class], type: Interceptor::API)]
class User extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private Balance $balance;


    #[Inject]
    private Lifetime $lifetime;

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
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy(...$this->query->getOrderBy($map, "user.id", "desc"));
        $get->setColumn("id", "username", "email", "avatar", "integral", "pid", "status", "balance", "withdraw_amount", "level_id");
        $raw = [];
        $data = $this->query->get($get, function (Builder $builder) use ($map, &$raw) {
            $builder = $builder->where("pid", $this->getUser()->id);

            $raw['user_count'] = (clone $builder)->count();
            $raw['user_total_balance'] = (clone $builder)->sum("balance");


            return $builder->with([
                'level',
                'lifetime' => function (HasOne $one) {
                    $one->with(['favoriteItem', 'shareItem']);
                }
            ]);
        });

        return $this->json(data: $data, ext: $raw);
    }


    /**
     * @return Response
     * @throws \Throwable
     */
    public function transfer(): Response
    {
        $id = $this->request->post('id', Filter::INTEGER);
        $amount = $this->request->post('amount');

        //转账操作
        Db::transaction(function () use ($amount, $id) {
            $this->balance->transfer($this->getUser()->id, $id, $amount);
            $this->lifetime->increment($id, "total_recharge_amount", $amount);
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);

        return $this->response->json();
    }

    /**
     * @return Response
     */
    #[Validator([
        [\App\Validator\User\User::class, ["userId", "levelId"]]
    ])]
    public function changeLevel(): Response
    {
        $levelId = $this->request->post('level_id', Filter::INTEGER);
        $userId = $this->request->post('user_id', Filter::INTEGER);
        \App\Model\User::query()->where("id", $userId)->where("pid", $this->getUser()->id)->update(['level_id' => $levelId]);
        return $this->response->json();
    }



}