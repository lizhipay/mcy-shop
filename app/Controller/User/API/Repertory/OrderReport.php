<?php
declare (strict_types=1);

namespace App\Controller\User\API\Repertory;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Entity\Report\Handle;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Supplier;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\OrderReport as Model;
use App\Model\OrderReportMessage;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Supplier::class], type: Interceptor::API)]
class OrderReport extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private \App\Service\User\OrderReport $orderReport;


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
        $get->setOrderBy("order_report.id", "desc");
        $get->setColumn("order_report.*");
        $data = $this->query->get($get, function (Builder $builder) use ($map) {
            $builder = $builder->with([
                "orderItem" => function (HasOne $one) {
                    $one->with([
                        'item' => function (HasOne $one) {
                            $one->select(['id', 'name', 'picture_thumb_url']);
                        },
                        'sku' => function (HasOne $one) {
                            $one->with([
                                'repertoryItemSku' => function (HasOne $one) {
                                    $one->select(['id', 'supply_price', 'cost', 'user_id', 'stock_price']);
                                }
                            ])->select(['id', 'name', 'picture_thumb_url', 'repertory_item_sku_id']);
                        },
                        'order' => function (HasOne $one) {
                            $one->select(['id', 'trade_no']);
                        }
                    ]);
                }
            ])->where("supply_id", $this->getUser()->id);

            if (isset($map['trade_no']) && $map['trade_no'] !== "") {
                $builder = $builder
                    ->leftJoin("order_item", "order_report.order_item_id", "=", "order_item.id")
                    ->leftJoin("order", "order_item.order_id", "=", "order.id")
                    ->where("order.trade_no", trim($map['trade_no']));
            }

            return $builder;
        });
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Supply\OrderReport::class, "reportId"]
    ])]
    public function message(): Response
    {
        $message = OrderReportMessage::query()->where("order_report_id", $this->request->post("report_id", Filter::INTEGER))->get();
        return $this->json(data: $message->toArray());
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Supply\OrderReport::class, ["reportId", "handleType", "message", "treasure", "refundAmount"]]
    ])]
    public function handle(): Response
    {
        $treasure = $this->request->post("treasure");
        $refundAmount = $this->request->post("refund_amount");
        $refundMerchantAmount = $this->request->post("refund_merchant_amount");
        $imageUrl = $this->request->post("image_url");

        $handle = new Handle(
            reportId: $this->request->post("report_id", Filter::INTEGER),
            type: $this->request->post("handle_type", Filter::INTEGER),
            message: $this->request->post("message"),
            role: 1
        );

        $treasure && $handle->setTreasure($treasure);
        $refundAmount && $handle->setRefundAmount($refundAmount);
        $refundMerchantAmount && $handle->setRefundMerchantAmount($refundMerchantAmount);
        $imageUrl && $handle->setImageUrl($imageUrl);

        $this->orderReport->handle($handle);
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Supply\OrderReport::class, "reportId"]
    ])]
    public function heartbeat(): Response
    {
        $reportId = $this->request->post("report_id", Filter::INTEGER);
        $message = OrderReportMessage::query()
            ->with(["orderReport" => function (HasOne $one) {
                $one->select(["id", "status", "handle_type"]);
            }])
            ->where("order_report_id", $reportId)
            ->orderBy("id", "desc")
            ->first();
        return $this->json(data: ["latest" => $message?->id, "order" => $message?->orderReport]);
    }
}