<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Controller\User\Base;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Interceptor\Identity;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\UserBankCard as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Identity::class], type: Interceptor::API)]
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
        $get = new Get(Model::class);
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder
                ->where("user_id", $this->getUser()->id)
                ->withSum("totalWithdraw as total_withdraw_amount", "amount")
                ->withSum("todayWithdraw as today_withdraw_amount", "amount")
                ->withSum("yesterdayWithdraw as yesterday_withdraw_amount", "amount")
                ->withSum("weekdayWithdraw as weekday_withdraw_amount", "amount")
                ->withSum("monthWithdraw as month_withdraw_amount", "amount")
                ->withSum("lastMonthWithdraw as last_month_withdraw_amount", "amount")
                ->with('bank');
        });
        return $this->json(data: ["list" => $data]);
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\UserBankCard::class, ['bankId', 'cardNo']]
    ])]
    public function save(): Response
    {
        $bankId = $this->request->post("bank_id", Filter::INTEGER);
        $cardNo = $this->request->post("card_no");
        $cardImage = $this->request->post("card_image");
        $this->bankCard->add($this->getUser()->id, $bankId, $cardNo, $cardImage);
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