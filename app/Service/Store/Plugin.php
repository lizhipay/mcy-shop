<?php
declare (strict_types=1);

namespace App\Service\Store;

use App\Entity\Store\Authentication;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Store\Bind\Plugin::class)]
interface Plugin
{
    /**
     * @param array $post
     * @param Authentication $authentication
     * @return void
     */
    public function createOrUpdate(array $post, Authentication $authentication): void;
}