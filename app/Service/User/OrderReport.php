<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Entity\Report\Handle;
use App\Entity\Report\Reply;
use Kernel\Annotation\Bind;
use App\Entity\Report\Order;

#[Bind(class: \App\Service\User\Bind\OrderReport::class)]
interface OrderReport
{

    /**
     * @param Order $order
     * @return void
     */
    public function apply(Order $order): void;

    /**
     * @param Handle $handle
     * @return void
     */
    public function handle(Handle $handle): void;


    /**
     * @param Reply $reply
     * @return void
     */
    public function reply(Reply $reply): void;
}