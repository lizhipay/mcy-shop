<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Shop;


use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Order;
use App\Service\Common\Query;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class OrderSummary extends Base
{

    #[Inject]
    private Query $query;


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function get(): Response
    {
        $dateType = (int)$this->request->post("equal-date_type");
        $page = (int)$this->request->post("page");
        $perPage = (int)$this->request->post("limit");
        $userId = $this->request->post("user_id", Filter::INTEGER);
        $startTime = $this->request->post("betweenStart-time");
        $endTime = $this->request->post("betweenEnd-time");

        $date = [0 => "DATE(`create_time`)", 1 => "YEARWEEK(`create_time`, 1)", 2 => "DATE_FORMAT(`create_time`, '%Y-%m')", 3 => "YEAR(`create_time`)"];

        $order = Order::with("user")->selectRaw("user_id,{$date[$dateType]} as date,
                SUM(CASE WHEN type = 0 THEN `total_amount` ELSE 0 END) as product_amount,
                SUM(CASE WHEN type = 1 THEN `total_amount` ELSE 0 END) as recharge_amount,
                SUM(CASE WHEN type = 2 THEN `total_amount` ELSE 0 END) as group_amount,
                SUM(CASE WHEN type = 3 THEN `total_amount` ELSE 0 END) as level_amount,
                SUM(CASE WHEN type = 49 THEN `total_amount` ELSE 0 END) as plugin_amount"
        )->groupBy(['date'])->where("status", 1)->orderBy("date", "desc");


        if ($userId > 0) {
            $order->where("user_id", $userId);
        } else {
            $order->whereNull("user_id");
        }

        if ($startTime) {
            $order = $order->where("create_time", ">=", $startTime);
        }

        if ($endTime) {
            $order = $order->where("create_time", "<=", $endTime);
        }

        $order = $order->get();
        $offset = ($page - 1) * $perPage;
        $pagedResults = $order->slice($offset, $perPage)->values();
        return $this->json(data: ["list" => $pagedResults->toArray(), "total" => $order->count()]);
    }
}