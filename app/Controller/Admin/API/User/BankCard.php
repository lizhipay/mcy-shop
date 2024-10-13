<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\User;

use App\Controller\Admin\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\UserBankCard as Model;
use App\Service\Common\Query;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class BankCard extends Base
{
    #[Inject]
    private Query $query;


    #[Inject]
    private \App\Service\User\BankCard $bankCard;


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function get(): Response
    {
        $map = $this->request->post();
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy(...$this->query->getOrderBy($map, "id", "desc"));
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder
                ->withSum("totalWithdraw as total_withdraw_amount", "amount")
                ->withSum("todayWithdraw as today_withdraw_amount", "amount")
                ->withSum("yesterdayWithdraw as yesterday_withdraw_amount", "amount")
                ->withSum("weekdayWithdraw as weekday_withdraw_amount", "amount")
                ->withSum("monthWithdraw as month_withdraw_amount", "amount")
                ->withSum("lastMonthWithdraw as last_month_withdraw_amount", "amount")
                ->with(['bank', 'user']);
        });
        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, "id"]
    ])]
    public function abnormality(): Response
    {
        $id = $this->request->post("id", Filter::INTEGER);
        $status = $this->request->post("status", Filter::INTEGER);
        $this->bankCard->abnormality($id, $status);
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    public function del(): Response
    {
        $delete = new Delete(Model::class, (array)$this->request->post("list"));
        $this->query->delete($delete);
        return $this->json();
    }
}