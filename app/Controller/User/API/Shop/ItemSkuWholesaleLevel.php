<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\ItemSkuWholesaleLevel as Model;
use App\Model\UserLevel;
use App\Service\Common\Query;
use App\Service\User\Ownership;
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

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class ItemSkuWholesaleLevel extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private Ownership $ownership;

    /**
     * @param int $id
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function get(int $id): Response
    {
        $post = $this->request->post();
        $get = new Get(UserLevel::class);
        $get->setWhere($post);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setColumn('id', 'name', 'sort', 'icon');
        $get->setOrderBy("sort", "asc");

        $data = $this->query->get($get, function (Builder $builder) use ($id) {
            $builder = $builder->where("user_id", $this->getUser()->id);
            return $builder->with(['itemSkuWholesaleLevel' => function (Relation $relation) use ($id) {
                $relation->where("wholesale_id", $id)->select(['id', 'price', 'status', 'level_id', 'dividend_amount']);
            }]);
        });
        return $this->json(data: $data);
    }

    /**
     * @param int $id
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\User\ItemSkuWholesale::class, "price"]
    ])]
    public function save(int $id): Response
    {
        $map = $this->request->post();

        $this->ownership->throw(
            $this->ownership->wholesale($this->getUser()->id, $id),
            $this->ownership->level($this->getUser()->id, (int)$map['id'])
        );

        try {
            $model = \App\Model\ItemSkuWholesaleLevel::query()->where("user_id", $this->getUser()->id)->where("wholesale_id", $id)->where("level_id", $map['id'])->first();
            if (!$model) {
                $model = new Model();
                $model->level_id = $map['id'];
                $model->wholesale_id = $id;
                $model->create_time = Date::current();
                $model->user_id = $this->getUser()->id;
            }
            foreach ($map as $k => $v) {
                $k = strtolower(trim($k));
                if (!in_array($k, ["id", "user_id"])) {
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