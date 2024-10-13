<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Shop;

use App\Controller\Admin\Base;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
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

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class ItemSkuWholesale extends Base
{

    #[Inject]
    private RepertoryOrder $order;

    #[Inject]
    private Query $query;


    /**
     * @param int $id
     * @return Response
     * @throws RuntimeException
     */
    public function get(int $id): Response
    {
        $itemSku = \App\Model\ItemSku::with(['user'])->find($id);

        /**
         * @var RepertoryItemSku $repertoryItemSku
         */
        $repertoryItemSku = RepertoryItemSku::query()->find($itemSku->repertory_item_sku_id);
        $data = Model::query()->orderBy("quantity", "asc")->where("sku_id", $id)->get()->toArray();
        foreach ($data as $index => $value) {
            $data[$index]['realtime_stock_price'] = $this->order->getAmount($itemSku->user, $repertoryItemSku, (int)$value['quantity']);
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
        [\App\Validator\Admin\ItemSku::class, "price"]
    ])]
    public function save(): Response
    {
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