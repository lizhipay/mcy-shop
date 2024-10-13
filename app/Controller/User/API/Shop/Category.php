<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Controller\User\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\Category as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;


#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class Category extends Base
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
        $get->setWhere($this->request->post());
        $get->setOrderBy(...$this->query->getOrderBy($this->request->post(), "sort", "asc"));
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->withCount(
                ["item as item_shelf_count" => function (Builder $relation) {
                    $relation->where("status", 1)->where("user_id", $this->getUser()->id);
                }, "item as item_all_count" => function (Builder $builder) {
                    return $builder->where("user_id", $this->getUser()->id);
                }]
            )->where("user_id", $this->getUser()->id);
        });
        return $this->json(data: ["list" => $data]);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        ['key' => 'name', 'rule' => 'require', 'message' => ['require' => '分类名称不能为空']]
    ])]
    public function save(): Response
    {
        $whitelist = ["name", "sort", "icon", "status", "pid"];
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $save->setMap($this->request->post(flags: Filter::NORMAL));
        $save->addForceMap("user_id", $this->getUser()->id);
        $save->setAddWhitelist(...$whitelist);
        $save->setModifiableWhitelist(...$whitelist);

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
        $delete->setWhere("user_id", $this->getUser()->id);
        $this->query->delete($delete);
        return $this->response->json(message: "删除成功");
    }
}