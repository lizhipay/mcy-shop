<?php
declare (strict_types=1);

namespace App\Entity\Store;

class Http
{
    public int $code;
    public string $message;
    public array $data;
    public array $origin;

    /**
     * @param int $code
     * @param string $message
     * @param array $data
     * @param array $origin
     */
    public function __construct(int $code, string $message, array $data = [], array $origin = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
        $this->origin = $origin;
    }
}