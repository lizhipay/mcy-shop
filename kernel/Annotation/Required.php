<?php
declare (strict_types=1);

namespace Kernel\Annotation;
#[\Attribute(\Attribute::TARGET_METHOD)]
class Required
{
    /**
     * @var int
     */
    public int $mode;
    public string $message;


    /**
     * @param string $message
     * @param int $mode
     */
    public function __construct(string $message, int $mode = \Kernel\Validator\Required::EXTREME)
    {
        $this->mode = $mode;
        $this->message = $message;
    }
}