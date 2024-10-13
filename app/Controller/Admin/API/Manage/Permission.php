<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Manage;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Permission as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Permission extends Base
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
        $get->setOrderBy("rank", "asc");
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->whereIn("type", [0, 1]);
        });
        return $this->json(data: ["list" => $data]);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function save(): Response
    {
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $map = $this->request->post();
        $save->setMap(map: $map, bypass: ["name", "pid", "rank", "icon"]);
        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->json();
    }
}