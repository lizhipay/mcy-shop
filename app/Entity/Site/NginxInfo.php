<?php
declare (strict_types=1);

namespace App\Entity\Site;

class NginxInfo
{
    public string $host;
    public string $pem;
    public string $key;
    public string $conf;
    public string $path;


    public function __construct(string $host, string $pem, string $key, string $conf, string $path)
    {
        $this->host = $host;
        $this->pem = $pem;
        $this->key = $key;
        $this->conf = $conf;
        $this->path = $path;
    }
}