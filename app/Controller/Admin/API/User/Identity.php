<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\User;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\UserIdentity as Model;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Identity extends Base
{
    #[Inject]
    private Query $query;

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
            $raw['count'] = (clone $builder)->count();
            $raw['not_reviewed_count'] = (clone $builder)->where("status", 0)->count();
            $raw['not_success_count'] = (clone $builder)->where("status", 1)->count();

            return $builder->with(["user" => function (Relation $relation) {
                $relation->select(["id", "username", "avatar"]);
            }]);
        });

        return $this->json(data: $data, ext: $raw);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Admin\Level::class, "name"]
    ])]
    public function save(): Response
    {
        $map = $this->request->post();
        $save = new Save(Model::class);
        $save->setMap($map, ["status"]);
        $save->disableAddable();
        $save->addForceMap("review_time", Date::current());
        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function del(): Response
    {
        $delete = new Delete(Model::class, (array)$this->request->post("list"));
        $this->query->delete($delete);
        return $this->json(message: "删除成功");
    }
}