<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

class Theme
{
    public string $name;
    public array $theme;

    /**
     * @param string $name
     * @param array $theme
     */
    public function __construct(string $name, array $theme)
    {
        $this->name = $name;
        $this->theme = $theme;
    }
}