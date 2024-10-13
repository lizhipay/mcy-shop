<?php
declare (strict_types=1);

namespace Kernel\Language\Entity;

class Language
{
    public string $preferred;

    /**
     * @param string $preferred
     */
    public function __construct(string $preferred)
    {
        $this->preferred = $preferred;
    }
}