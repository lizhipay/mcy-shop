<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

use Kernel\Component\ToArray;
use Kernel\Waf\Firewall;

class Attr
{
    use ToArray;

    public string $name;
    public string $value;


    public function __construct(string $name, string $value)
    {
        $this->name = Firewall::inst()->xssKiller($name);
        $this->value = Firewall::inst()->xssKiller($value);
    }
}