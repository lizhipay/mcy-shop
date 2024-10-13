<?php
declare (strict_types=1);

namespace App\Service\Store;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Store\Bind\Identity::class)]
interface Identity
{

    /**
     * @param Authentication $authentication
     * @param string $tradeNo
     * @return array
     */
    public function status(Authentication $authentication, string $tradeNo = ""): array;


    /**
     * @param string $certName
     * @param string $certNo
     * @param Authentication $authentication
     * @return mixed
     */
    public function certification(string $certName, string $certNo, Authentication $authentication): string;
}