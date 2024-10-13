<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Model\UserBankCard;
use Kernel\Exception\ServiceException;
use Kernel\Util\Date;

class BankCard implements \App\Service\User\BankCard
{

    /**
     * @param int $userId
     * @param int $bankId
     * @param string $cardNo
     * @param string|null $cardImage
     * @return void
     * @throws ServiceException
     */
    public function add(int $userId, int $bankId, string $cardNo, ?string $cardImage = null): void
    {
        if (UserBankCard::query()->where("card_no", $cardNo)->exists()) {
            throw new ServiceException("该银行卡无法使用");
        }

        $userBankCard = new UserBankCard();

        if ($cardImage) {
            $file = BASE_PATH . $cardImage;

            if (!is_file($file)) {
                throw new ServiceException("该银行卡无法使用");
            }

            $hash = md5_file($file);

            if (UserBankCard::query()->where("card_image_hash", $hash)->exists()) {
                throw new ServiceException("该银行卡无法使用");
            }

            $userBankCard->card_image = $cardImage;
            $userBankCard->card_image_hash = $hash;
        }


        $userBankCard->user_id = $userId;
        $userBankCard->bank_id = $bankId;
        $userBankCard->card_no = $cardNo;
        $userBankCard->status = 1;
        $userBankCard->create_time = Date::current();
        $userBankCard->save();
    }


    /**
     * @param int $cardId
     * @param int $status
     * @return void
     */
    public function abnormality(int $cardId, int $status = 0): void
    {
        UserBankCard::query()->where("id", $cardId)->update(['status' => $status]);
    }

    /**
     * @param int $cardId
     * @return void
     */
    public function del(int $cardId): void
    {
        UserBankCard::query()->where("id", $cardId)->delete();
    }


    /**
     * @param int $userId
     * @return \App\Entity\User\BankCard[]
     */
    public function list(int $userId): array
    {
        $cards = UserBankCard::with("bank")->where("user_id", $userId)->get();

        $array = [];
        foreach ($cards as $card) {
            if ($card->status != 1 || $card?->bank?->status != 1) {
                continue;
            }

            $array[] = new \App\Entity\User\BankCard($card->id, $card->bank->name, $card->bank->icon, $card->card_no);
        }

        return $array;
    }
}