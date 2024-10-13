<?php
declare(strict_types=1);

namespace App\Controller\Admin\API\Manage;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Manage as ManageModel;
use App\Model\ManageRole;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;
use Kernel\Util\Str;
use Kernel\Util\Verify;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Manage extends Base
{
    #[Inject]
    private Query $query;

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function get(): Response
    {
        $get = new Get(ManageModel::class);
        $get->setWhere($this->request->post());
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->where("type", 1)->with(['log' => function (Relation $relation) {
                $relation->limit(5)->orderBy("id", "desc");
            }, 'role']);
        });
        return $this->json(data: $data);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\Admin\Manage::class, ["id", "email", "nickname", "password"]]
    ])]
    public function save(): Response
    {
        $map = $this->request->post();

        if (!isset($map['id'])) {
            Verify::isBlank($map['password'], "密码不能为空");
            $map['salt'] = Str::generateRandStr();
            $map['password'] = Str::generatePassword($map['password'], $map['salt']);
            $map['create_time'] = Date::current();
        } else {
            if (isset($map['password']) && $map['password'] !== "") {
                $manage = \App\Model\Manage::find($map['id']);
                $map['password'] = Str::generatePassword($map['password'], $manage->salt);
            }
        }

        $save = new Save(ManageModel::class);
        $save->setMap($map);

        $save->addForceMap("type", 1);

        $save->setMiddle('role', ManageRole::class, 'role_id', 'manage_id');
        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }

        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function del(): Response
    {
        $delete = new Delete(ManageModel::class, (array)$this->request->post("list"));
        $this->query->delete($delete);
        return $this->json();
    }
}