<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryCategory as Model;
use App\Service\Common\Query;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Category extends Base
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
        $get = new Get(Model::class);
        $get->setWhere($this->request->post());
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy(...$this->query->getOrderBy($this->request->post(), "sort", "asc"));
        $data = $this->query->get($get);
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\Category::class, "name"]
    ])]
    public function save(): Response
    {
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $save->setMap($this->request->post());
        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }


    /**
     * @return Response
     */
    public function del(): Response
    {
        $delete = new Delete(Model::class, (array)$this->request->post("list"));
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }
}