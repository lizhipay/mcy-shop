<?php
declare(strict_types=1);

namespace App\Controller\User\API\Repertory;

use App\Controller\User\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Supplier;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\RepertoryItemMarkupTemplate as Model;
use App\Service\Common\Query;
use App\Service\Common\RepertoryItem;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Call;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Supplier::class], type: Interceptor::API)]
class ItemMarkupTemplate extends Base
{

    #[Inject]
    private Query $query;

    #[Inject]
    private RepertoryItem $repertoryItem;

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
        [\App\Validator\User\ItemMarkupTemplate::class, "id"],
        [\App\Validator\Admin\ItemMarkupTemplate::class, ["name", "driftBaseAmount", "driftValue", "driftModel"]]
    ])]
    public function save(): Response
    {
        $map = $this->request->post();
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $save->setMap($map);
        $save->addForceMap("user_id", $this->getUser()->id);
        try {
            /**
             * @var Model $origin
             */
            $origin = isset($map['id']) ? Model::query()->where("user_id", $this->getUser()->id)->find($map['id']) : null;

            /**
             * @var Model $saved
             */
            $saved = $this->query->save($save);

            if ($origin && $this->repertoryItem->checkForceSyncRemoteItemPrice($origin->toArray(), $saved->toArray())) {
                //强制同步价格
                Call::create(function () use ($saved) {
                    $repertoryItems = \App\Model\RepertoryItem::query()
                        ->where("markup_mode", "!=", 0)
                        ->where("markup_template_id", $saved->id)
                        ->get();
                    foreach ($repertoryItems as $repertoryItem) {
                        $this->repertoryItem->forceSyncRemoteItemPrice($repertoryItem);
                    }
                });
            }

        } catch (\Exception $exception) {
            throw new JSONException(Resolver::make($exception)->getMessage());
        }
        return $this->json();
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
        return $this->json();
    }
}