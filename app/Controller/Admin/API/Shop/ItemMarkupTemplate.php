<?php
declare(strict_types=1);

namespace App\Controller\Admin\API\Shop;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\ItemMarkupTemplate as Model;
use App\Service\Common\Query;
use App\Service\Common\RepertoryItem;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class ItemMarkupTemplate extends Base
{

    #[Inject]
    private Query $query;

    #[Inject]
    private RepertoryItem $repertoryItem;

    #[Inject]
    private \App\Service\User\Item $item;


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
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");

        $data = $this->query->get($get, function (Builder $builder) use ($map) {
            if (isset($map['user_id']) && $map['user_id'] > 0) {
                $builder = $builder->where("user_id", $map['user_id']);
            } else {
                $builder = $builder->whereNull("user_id");
            }
            return $builder->with(["user" => function (Relation $relation) {
                $relation->select(["id", "username", "avatar"]);
            }]);
        });

        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        [\App\Validator\Admin\ItemMarkupTemplate::class, ["name", "driftBaseAmount", "driftValue", "driftModel"]]
    ])]
    public function save(): Response
    {
        $map = $this->request->post();
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $save->setMap($map);
        try {
            $origin = isset($map['id']) ? Model::find($map['id']) : null;
            $saved = $this->query->save($save);

            if ($origin && $this->repertoryItem->checkForceSyncRemoteItemPrice($origin->toArray(), $saved->toArray())) {
                $this->item->syncRepertoryItemForMarkupTemplate($origin->id); //同步模版
            }
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