<?php
declare (strict_types=1);

namespace Kernel\Annotation;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Regex
{
    /**
     * @var string
     */
    public string $regex;
    public string $message;

    /**
     * Hook constructor.
     * @param string $regex
     * @param string $message
     */
    public function __construct(string $regex, string $message)
    {
        $this->regex = $regex;
        $this->message = $message;
    }
}