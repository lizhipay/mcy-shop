<?php
declare(strict_types=1);

namespace App\Controller\User\API\Sales;

use App\Controller\User\Base;
use App\Model\SalesRecord;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

/**
 * 前台销售登记API（免登录）
 */
class Register extends Base
{
    /**
     * 提交销售登记
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    public function submit(): Response
    {
        $salesman = trim((string)$this->request->post("salesman"));
        $productName = trim((string)$this->request->post("product_name"));
        $orderAmount = $this->request->post("order_amount");
        $remark = trim((string)$this->request->post("remark"));

        if (empty($salesman)) {
            throw new JSONException("请输入销售人名称");
        }
        if (empty($productName)) {
            throw new JSONException("请输入商品名称");
        }
        if (!is_numeric($orderAmount) || floatval($orderAmount) <= 0) {
            throw new JSONException("请输入有效的销售金额");
        }

        SalesRecord::query()->create([
            'salesman' => $salesman,
            'product_name' => $productName,
            'order_amount' => round(floatval($orderAmount), 2),
            'remark' => $remark ?: null,
            'status' => 1,
        ]);

        return $this->json(message: "登记成功");
    }
}
