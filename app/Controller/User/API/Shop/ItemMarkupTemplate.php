<?php
declare(strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Controller\User\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\ItemMarkupTemplate as Model;
use App\Service\Common\Query;
use App\Service\Common\RepertoryItem;
use App\Service\User\Ownership;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class ItemMarkupTemplate extends Base
{

    #[Inject]
    private Query $query;

    #[Inject]
    private Ownership $ownership;

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
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->where("user_id", $this->getUser()->id);
        });
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        [\App\Validator\Admin\ItemMarkupTemplate::class, ["name", "driftBaseAmount", "driftValue", "driftModel"]]
    ])]
    public function save(): Response
    {
        $map = $this->request->post();
        isset($map['id']) && $this->ownership->throw($this->ownership->markup($this->getUser()->id, (int)$map['id']));
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $save->setMap($map);
        $save->addForceMap("user_id", $this->getUser()->id);
        try {
            $origin = isset($map['id']) ? Model::find($map['id']) : null;
            $saved = $this->query->save($save);
            if ($origin && $this->repertoryItem->checkForceSyncRemoteItemPrice($origin->toArray(), $saved->toArray())) {
                $this->item->syncRepertoryItemForMarkupTemplate($origin->id);
            }
        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->json(message: "保存成功");
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function del(): Response
    {
        $delete = new Delete(Model::class, (array)$this->request->post("list"));
        $delete->setWhere("user_id", $this->getUser()->id);
        $this->query->delete($delete);
        return $this->json(message: "删除成功");
    }
}