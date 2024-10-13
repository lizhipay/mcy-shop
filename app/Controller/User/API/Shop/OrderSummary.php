<?php
declare (strict_types=1);

namespace App\Controller\User\API\Shop;


use App\Controller\User\Base;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\Order;
use App\Service\Common\Query;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
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
        $startTime = $this->request->post("betweenStart-time");
        $endTime = $this->request->post("betweenEnd-time");

        $date = [0 => "DATE(`create_time`)", 1 => "YEARWEEK(`create_time`, 1)", 2 => "DATE_FORMAT(`create_time`, '%Y-%m')", 3 => "YEAR(`create_time`)"];

        $order = Order::selectRaw("{$date[$dateType]} as date,SUM(`total_amount`) as amount"
        )->groupBy(['date'])->where("type" , 0)->where("status", 1)->where("user_id", $this->getUser()->id)->orderBy("date", "desc");

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