<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;

use App\Controller\User\Base;
use App\Entity\Query\Save;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\ItemSkuWholesale as Model;
use App\Model\RepertoryItemSku;
use App\Service\Common\Query;
use App\Service\Common\RepertoryOrder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Database\Exception\Resolver;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class ItemSkuWholesale extends Base
{

    #[Inject]
    private RepertoryOrder $order;

    #[Inject]
    private Query $query;


    /**
     * @param int $id
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    public function get(int $id): Response
    {
        $itemSku = \App\Model\ItemSku::query()->where("user_id", $this->getUser()->id)->find($id);

        if (!$itemSku) {
            throw new JSONException("权限不足");
        }

        /**
         * @var RepertoryItemSku $repertoryItemSku
         */
        $repertoryItemSku = RepertoryItemSku::query()->find($itemSku->repertory_item_sku_id);
        $data = Model::query()
            ->where("user_id", $this->getUser()->id)
            ->where("sku_id", $id)
            ->orderBy("quantity", "asc")
            ->get(["id", "sku_id", "quantity", "price", "stock_price", "dividend_amount"])->toArray();
        foreach ($data as $index => $value) {
            $data[$index]['realtime_stock_price'] = $this->order->getAmount($this->getUser(), $repertoryItemSku, (int)$value['quantity']);
        }
        return $this->json(data: ['list' => $data]);
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Validator([
        ['key' => 'price', 'rule' => 'notZero', 'message' => ['notZero' => '零售价，必须大于0']],
    ])]
    public function save(): Response
    {
        $id = (int)$this->request->post("id");

        /**
         * @var Model $model
         */
        $model = Model::query()->where("user_id", $this->getUser()->id)->find($id);

        if (!$model) {
            throw new JSONException("权限不足");
        }

        $save = new Save(Model::class);
        $save->setMap($this->request->post(), bypass: ["price", "dividend_amount"]);
        $save->disableAddable();
        try {
            $this->query->save($save);
        } catch (\Throwable $e) {
            throw new JSONException(Resolver::make($e)->getMessage());
        }

        return $this->json();
    }
}