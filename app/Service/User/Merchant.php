<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Model\User;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Merchant::class)]
interface Merchant
{


}