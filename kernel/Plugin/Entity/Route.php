<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

class Route
{
    /**
     * @var string
     */
    public string $usr;


    /**
     * @var string
     */
    public string $name;


    /**
     * @param string $name
     * @param string $usr
     */
    public function __construct(string $name, string $usr)
    {
        $this->usr = $usr;
        $this->name = $name;
    }
}