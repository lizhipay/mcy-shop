<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

use Kernel\Component\ToArray;
use Kernel\Waf\Firewall;

class Item
{

    use ToArray;

    public string $name;
    public string $introduce;
    public string $pictureUrl;
    /**
     * @var Widget[]
     */
    public array $widgets = [];
    /**
     * @var Attr[]
     */
    public array $attr = [];
    public string $category;

    /**
     * @var string
     */
    public string $uniqueId;

    /**
     * @var Sku[]
     */
    public array $skus;

    /**
     * @var array
     */
    public array $versions = [];

    /**
     * @var array
     */
    public array $options = [];

    /**
     * @param string|int|float $uniqueId
     * @param string $category
     * @param string $name
     * @param string $introduce
     * @param string $pictureUrl
     * @param array $skus
     * @throws \ReflectionException
     */
    public function __construct(string|int|float $uniqueId, string $category, string $name, string $introduce, string $pictureUrl, array $skus)
    {
        $this->name = Firewall::inst()->xssKiller($name);
        $this->introduce = Firewall::inst()->xssKiller($introduce);
        $this->pictureUrl = strip_tags($pictureUrl);
        $this->skus = $skus;
        $this->category = Firewall::inst()->xssKiller($category);
        $this->uniqueId = md5((string)$uniqueId);

        $this->versions["name"] = md5((string)$this->name);
        $this->versions["introduce"] = md5((string)$this->introduce);
        $this->versions["picture_url"] = md5($this->pictureUrl);
    }

    /**
     * @param Widget[] $widgets
     */
    public function setWidgets(array $widgets): void
    {
        $this->widgets = $widgets;
    }

    /**
     * @param Attr[] $attr
     */
    public function setAttr(array $attr): void
    {
        $this->attr = $attr;
    }


    /**
     * @param array $options
     * @throws \ReflectionException
     */
    public function setOptions(array $options): void
    {
        $this->options = Firewall::inst()->xssKiller($options);
    }
}