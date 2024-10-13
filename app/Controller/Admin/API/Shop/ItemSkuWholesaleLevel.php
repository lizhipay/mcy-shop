<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Shop;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\ItemSkuWholesaleLevel as Model;
use App\Model\UserLevel;
use App\Service\Common\Query;
use App\Validator\Common;
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

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class ItemSkuWholesaleLevel extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @param int $id
     * @param int $userId
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(int $id, int $userId): Response
    {
        $post = $this->request->post();
        $get = new Get(UserLevel::class);
        $get->setWhere($post);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setColumn('id', 'name', 'sort', 'icon');
        $get->setOrderBy(...$this->query->getOrderBy($post, "sort", "asc"));

        $data = $this->query->get($get, function (Builder $builder) use ($id, $userId) {
            if ($userId == 0) {
                $builder = $builder->whereNull("user_id");
            } else {
                $builder = $builder->where("user_id", $userId);
            }

            return $builder->with(['itemSkuWholesaleLevel' => function (Relation $relation) use ($id) {
                $relation->where("wholesale_id", $id)->select(['id', 'price', 'status', 'level_id', "dividend_amount"]);
            }]);
        });

        return $this->json(data: $data);
    }

    /**
     * @param int $id
     * @param int $userId
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\Admin\ItemSku::class, "price"]
    ])]
    public function save(int $id, int $userId): Response
    {
        $map = $this->request->post();

        try {
            $model = \App\Model\ItemSkuWholesaleLevel::query()->where("wholesale_id", $id)->where("level_id", $map['id'])->first();
            if (!$model) {
                $model = new Model();
                $model->level_id = $map['id'];
                $model->wholesale_id = $id;
                $model->create_time = Date::current();
                if ($userId > 0) {
                    $model->user_id = $userId;
                }
            }
            foreach ($map as $k => $v) {
                if ($k != "id") {
                    $model->$k = $v;
                }
            }
            $model->save();
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }
}