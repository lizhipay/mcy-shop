<?php
declare (strict_types=1);

namespace App\Entity\Store;

use Kernel\Component\ToArray;

class UpdateLog
{
    use ToArray;

    public string $hash;
    public string $log;


    /**
     * @param string $hash
     * @param string $log
     */
    public function __construct(string $hash, string $log)
    {
        $this->hash = $hash;
        $this->log = $log;
    }
}