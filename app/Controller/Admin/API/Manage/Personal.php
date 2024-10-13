<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Manage;

use App\Controller\Admin\Base;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\ManageLoginLog;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Str;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Personal extends Base
{

    #[Inject]
    private Query $query;

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Admin\Personal::class, ["avatar", "currentPassword", "newPassword", "reNewPassword"]]
    ])]
    public function edit(): Response
    {

        $password = $this->request->post("new_password");
        $data = [
            "avatar" => $this->request->post("avatar")
        ];

        if ($password) {
            $data["password"] = Str::generatePassword($password, $this->getManage()->salt);
        }

        \App\Model\Manage::query()->where("id", $this->getManage()->id)->update($data);
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */

    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function loginLog(): Response
    {
        $map = $this->request->post();
        $get = new Get(ManageLoginLog::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");
        $data = $this->query->get($get, function (Builder $builder) use ($map) {
            return $builder->where("manage_id", $this->getManage()->id);
        });
        return $this->json(data: $data);
    }
}