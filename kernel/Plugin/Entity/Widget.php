<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

use Kernel\Component\ToArray;

class Widget
{
    use ToArray;

    public string $title;
    public string $name;
    public string $placeholder;
    public string $type;
    public ?string $regex = null;
    public ?string $error = null;
    public ?string $data = null;

    public function __construct(string $type, string $title, string $name, string $placeholder)
    {
        $this->title = strip_tags($title);
        $this->name = strip_tags($name);
        $this->placeholder = strip_tags($placeholder);
        $this->type = strip_tags($type);
    }


    /**
     * @param string $regex
     */
    public function setRegex(string $regex): void
    {
        $this->regex = strip_tags($regex);
    }

    /**
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->error = strip_tags($error);
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = strip_tags($data);
    }
}