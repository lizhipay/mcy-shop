<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\User;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
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
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Str;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
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
        $raw = [];
        $data = $this->query->get($get, function (Builder $builder) use ($map, &$raw) {

            if (isset($map['display_scope'])) {
                if ($map['display_scope'] == 1) {
                    $builder = $builder->whereNull("pid");
                } elseif ($map['display_scope'] == 2) {
                    if (isset($map['user_id']) && $map['user_id'] > 0) {
                        $builder = $builder->where("pid", $map['user_id']);
                    } else {
                        $builder = $builder->whereNotNull("pid");
                    }
                }
            }


            $raw['user_count'] = (clone $builder)->count();
            $raw['user_total_balance'] = (clone $builder)->sum("balance");


            return $builder->with([
                'parent' => function (HasOne $query) {
                    $query->select(['id', 'username', 'avatar']);
                },
                'group', 'level',
                'lifetime' => function (HasOne $one) {
                    $one->with(['favoriteItem', 'shareItem']);
                }
            ]);
        });

        return $this->json(data: $data, ext: $raw);
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function del(): Response
    {
        $delete = new Delete(\App\Model\User::class, (array)$this->request->post("list"));
        $this->query->delete($delete);
        return $this->json();
    }

    /**
     * @return Response
     */
    public function balanceChange(): Response
    {
        $type = $this->request->post('type', Filter::INTEGER);
        $id = $this->request->post('id', Filter::INTEGER);
        $amount = $this->request->post('amount');
        $remark = $this->request->post('remark');
        $isWithdraw = $this->request->post('is_withdraw') == 1;
        $isLifetime = $this->request->post('is_lifetime') == 1;

        if ($type == 0) {
            $this->balance->add(userId: $id, amount: $amount, type: \App\Const\Balance::TYPE_MANUAL, isWithdraw: $isWithdraw, remark: $remark);
            if ($isLifetime) {
                $this->lifetime->increment($id, "total_recharge_amount", $amount);
            }
        } else {
            $this->balance->deduct(userId: $id, amount: $amount, type: \App\Const\Balance::TYPE_MANUAL, remark: $remark);
        }

        return $this->response->json();
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\User::class, "password"]
    ])]
    public function save(): Response
    {
        $post = $this->request->post();
        $save = new Save(Model::class);
        $save->setMap($post, ['username', 'email', 'avatar', 'status', 'note', 'group_id', 'level_id', 'withdraw_amount']);
        try {
            //重置密码
            if (isset($post['password']) && $post['password'] != "") {

                if (isset($post['id'])) {
                    /**
                     * @var Model $user
                     */
                    $user = Model::query()->find($post['id']);
                    $save->addForceMap("password", Str::generatePassword($post['password'], $user?->salt));
                } else {
                    $salt = Str::generateRandStr();
                    $save->addForceMap("salt", $salt);
                    $save->addForceMap("password", Str::generatePassword($post['password'], $salt));
                    $save->addForceMap("api_code", strtoupper(Str::generateRandStr(6)));
                    $save->addForceMap("app_key", strtoupper(Str::generateRandStr(16)));
                }
            }

            $model = $this->query->save($save);

            if (!isset($post['id'])) {
                //创建生涯
                $this->lifetime->create($model->id, "127.0.0.1", 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36');
            }
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }
}