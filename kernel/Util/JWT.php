<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Component\Singleton;

class JWT
{
    use Singleton;


    /**
     * @param string $jwt
     * @return array
     */
    public function getHead(string $jwt): array
    {
        $arr = explode(".", $jwt);
        if (count($arr) != 3) {
            return [];
        }

        $head = base64_decode($arr[0]);
        return $head ? (array)json_decode($head, true) : [];
    }
}