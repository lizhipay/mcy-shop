<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\User;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Bank as Model;
use App\Service\Common\Query;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Bank extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function get(): Response
    {
        $get = new Get(Model::class);
        $data = $this->query->get($get);
        return $this->json(data: ["list" => $data]);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\Bank::class, ['name', 'code']]
    ])]
    public function save(): Response
    {
        $save = new Save(Model::class);
        $map = $this->request->post();
        $save->setMap($map);
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