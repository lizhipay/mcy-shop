<?php
declare (strict_types=1);

namespace App\Controller\User\API\Report;

use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Entity\Report\Reply;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\OrderReport as Model;
use App\Model\OrderReportMessage;
use App\Service\Common\Query;
use App\Service\User\OrderReport;
use App\Validator\Common;
use App\Validator\User\Report;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class Order extends Base
{
    #[Inject]
    private Query $query;

    #[Inject]
    private OrderReport $orderReport;

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
            $builder = $builder
                ->where("order_report.customer_id", $this->getUser()->id)
                ->with([
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
                ]);

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
        [\App\Validator\Admin\OrderReport::class, "reportId"]
    ])]
    public function message(): Response
    {
        $message = OrderReportMessage::query()
            ->where("order_report_message.order_report_id", $this->request->post("report_id", Filter::INTEGER))
            ->leftJoin("order_report", "order_report_message.order_report_id", "=", "order_report.id")
            ->where("order_report.customer_id", $this->getUser()->id)
            ->get(["order_report_message.*"]);
        return $this->json(data: $message->toArray());
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Admin\OrderReport::class, ["reportId"]]
    ])]
    public function heartbeat(): Response
    {
        $reportId = $this->request->post("report_id", Filter::INTEGER);
        /**
         * @var Model $orderReport
         */
        $orderReport = Model::query()->find($reportId, ["id", "customer_id", "status", "handle_type"]);
        if ($orderReport?->customer_id != $this->getUser()->id) {
            throw new JSONException("订单不存在");
        }

        $message = OrderReportMessage::query()->where("order_report_id", $reportId)->orderBy("id", "desc")->first();
        return $this->json(data: ["latest" => $message?->id, "order" => $orderReport]);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\Admin\OrderReport::class, ["reportId", "message"]]
    ])]
    public function reply(): Response
    {
        $imageUrl = $this->request->post("image_url");
        $reply = new Reply(
            userId: $this->getUser()->id,
            reportId: $this->request->post("report_id", Filter::INTEGER),
            message: $this->request->post("message")
        );

        $imageUrl && $reply->setImageUrl($imageUrl);

        $this->orderReport->reply($reply);

        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, "id"],
        [Report::class, ["type", "expect", "message"]]
    ])]
    public function apply(): Response
    {
        $imageUrl = $this->request->post("image_url");
        $order = new \App\Entity\Report\Order(
            $this->request->post("id", Filter::INTEGER),
            $this->getUser()->id,
            $this->request->post("type", Filter::INTEGER),
            $this->request->post("expect", Filter::INTEGER),
            $this->request->post("message"),
        );
        $imageUrl && $order->setImageUrl($imageUrl);
        $this->orderReport->apply($order);
        return $this->json();
    }
}