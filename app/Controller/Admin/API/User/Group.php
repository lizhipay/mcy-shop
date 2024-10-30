<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\User;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\UserGroup as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Group extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function get(): Response
    {
        $map = $this->request->post();
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setOrderBy("sort", "asc");

        $data = $this->query->get($get, function (Builder $builder) use ($map) {
            return $builder->withCount('user');
        });

        return $this->json(data: ['list' => $data]);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\Group::class, ["icon", "name"]]
    ])]
    public function save(): Response
    {
        $map = $this->request->post();
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $save->setMap($map, ["icon", "name", "sort", "is_merchant", "is_supplier", "is_upgradable", "price", "tax_ratio", "dividend_amount"]);
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
    #[Validator([
        [\App\Validator\Common::class, "id"]
    ])]
    public function del(): Response
    {
        $id = $this->request->post("id", Filter::INTEGER);

        if (\App\Model\User::query()->where("group_id", $id)->exists()) {
            throw new JSONException("该权限组下有商家，无法删除");
        }

        $delete = new Delete(Model::class, [$id]);
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }
}