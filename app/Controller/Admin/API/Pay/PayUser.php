<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Pay;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\PayUser as Model;
use App\Model\User;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class PayUser extends Base
{

    #[Inject]
    private Query $query;


    /**
     * @param string $id
     * @param string $type
     * @return Response
     * @throws RuntimeException
     */
    public function get(string $id, string $type): Response
    {
        $columns = ["fee", "status"];
        $get = new Get(User::class);
        $get->setWhere($this->request->post());
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setColumn('id', 'username', 'avatar');

        $data = $this->query->get($get, function (Builder $builder) use ($type, $id) {
            return $builder->with(['payUser' => function (Relation $relation) use ($id, $type) {
                $relation->where($type == "real" ? "pay_id" : "temp_id", $id);
            }]);
        });

        foreach ($data['list'] as &$v) {
            if ($v['pay_user']) {
                foreach ($columns as $column) {
                    $v[$column] = $v['pay_user'][$column];
                }
                unset($v['pay_group']);
            }
        }
        return $this->json(data: $data);
    }


    /**
     * @param string $payId
     * @param string $type
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function save(string $payId, string $type): Response
    {
        $map = $this->request->post();
        try {
            $field = $type == "real" ? "pay_id" : "temp_id";
            $model = Model::query()->where($field, $payId)->where("user_id", $map['id'])->first();
            if (!$model) {
                $model = new Model();
                $model->user_id = $map['id'];
                $model->$field = $payId;
                $model->create_time = Date::current();
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
        return $this->json();
    }
}