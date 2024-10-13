<?php
declare (strict_types=1);

namespace Kernel\Annotation;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Hook
{
    /**
     * @var int
     */
    public int $point;
    public int $weight = 100;

    /**
     * Hook constructor.
     * @param int $point
     * @param int $weight
     */
    public function __construct(int $point, int $weight = 100)
    {
        $this->point = $point;
        $this->weight = $weight;
    }
}