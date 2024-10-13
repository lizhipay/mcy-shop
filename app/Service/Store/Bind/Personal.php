<?php
declare (strict_types=1);

namespace App\Service\Store\Bind;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Inject;

class Personal implements \App\Service\Store\Personal
{

    #[Inject]
    private \App\Service\Store\Http $http;

    /**
     * @param Authentication $authentication
     * @return array
     */
    public function getInfo(Authentication $authentication): array
    {
        return $this->http->request(url: "/user/personal/info", authentication: $authentication)->data;
    }
}