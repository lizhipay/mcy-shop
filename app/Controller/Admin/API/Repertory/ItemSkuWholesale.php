<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Repertory;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Save;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryItemSkuWholesale as Model;
use App\Service\Common\Query;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class ItemSkuWholesale extends Base
{
    #[Inject]
    private Query $query;


    /**
     * @param int $id
     * @return Response
     * @throws RuntimeException
     */
    public function get(int $id): Response
    {
        $data = Model::query()->orderBy("quantity", "asc")->where("sku_id", $id)->get()->toArray();
        return $this->json(data: ['list' => $data]);
    }


    /**
     * @return Response
     * @throws JSONException
     */
    #[Validator([
        ['key' => 'stock_price', 'rule' => 'notZero', 'message' => ['notZero' => '进货价，必须大于0']],
    ])]
    public function save(): Response
    {
        $save = new Save(Model::class);
        $save->enableCreateTime();
        $map = $this->request->post();


        if (!isset($map['id'])) {
            /**
             * @var RepertoryItemSku $repertoryItemSku
             */
            $repertoryItemSku = RepertoryItemSku::query()->find($map['sku_id']);

            if (!$repertoryItemSku) {
                throw new JSONException("SKU 不存在");
            }


            $repertoryItemSku->user_id && $map['user_id'] = $repertoryItemSku->user_id;
        }

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