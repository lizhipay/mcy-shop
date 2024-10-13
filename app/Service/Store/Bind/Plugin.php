<?php
declare (strict_types=1);

namespace App\Service\Store\Bind;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Inject;
use Kernel\Exception\ServiceException;

class Plugin implements \App\Service\Store\Plugin
{


    #[Inject]
    private \App\Service\Store\Http $http;

    /**
     * @param array $post
     * @param Authentication $authentication
     * @return void
     * @throws ServiceException
     */
    public function createOrUpdate(array $post, Authentication $authentication): void
    {
        $http = $this->http->request("/store/plugin/save", $post, $authentication);
        if ($http->code != 200) {
            throw new ServiceException($http->message);
        }
    }
} 