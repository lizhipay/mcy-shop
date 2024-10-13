<?php
declare(strict_types=1);

namespace App\Controller\Admin\API\Manage;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Role as RoleModel;
use App\Model\RolePermission;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Role extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function get(): Response
    {
        $get = new Get(RoleModel::class);
        $get->setWhere($this->request->post());
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->with(['permission' => function (BelongsToMany $relation) {
                $relation->select(['permission.id']);
            }]);
        });
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    public function save(): Response
    {
        $save = new Save(RoleModel::class);
        $save->enableCreateTime();
        $save->setMap($this->request->post());
        $save->setMiddle('permission', RolePermission::class, 'permission_id', 'role_id');
        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }


    /**
     * @return Response
     * @throws JSONException
     */
    public function del(): Response
    {
        $list = (array)$this->request->post("list");
        foreach ($list as $id) {
            if ($id == 1) {
                throw new JSONException("超级管理员角色不可删除");
            }
        }
        $delete = new Delete(RoleModel::class, $list);
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }

}