<?php
declare(strict_types=1);

namespace Kernel\Annotation;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Thread
{

    /**
     * @var string
     */
    public string $name;

    /**
     * @var int
     */
    public int $num;

    /**
     * @param string $name
     * @param int $num
     */
    public function __construct(string $name, int $num = 1)
    {
        $this->name = $name;
        $this->num = $num;
    }
}