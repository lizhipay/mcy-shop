<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\User;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\UserBill;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Bill extends Base
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
        $get = new Get(UserBill::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy(...$this->query->getOrderBy($map, "id", "desc"));
        $data = $this->query->get($get, function (Builder $builder) use ($map) {
            if (isset($map['user_id']) && $map['user_id'] > 0) {
                $builder = $builder->where("user_id", $map['user_id']);
            }

            return $builder->with(['user']);
        });
        return $this->json(data: $data);
    }
}