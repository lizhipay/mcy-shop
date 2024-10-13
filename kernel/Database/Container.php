<?php
declare(strict_types=1);

namespace Kernel\Database;


use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Psr\Container\ContainerInterface;
use Throwable;

class Container implements ContainerInterface
{
    use Singleton;

    /**
     * @var array|string[]
     */
    public array $dependencies = [
        \Hyperf\Contract\LengthAwarePaginatorInterface::class => 'Hyperf\\Paginator\\LengthAwarePaginator'
    ];


    /**
     * @param string $id
     *
     * @return callable|mixed|string|null
     * @throws Throwable
     */
    public function get(string $id): mixed
    {
        return Di::instance()->get($id);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return Di::instance()->has($id);
    }


    /**
     * @param string $name
     * @param array $parameters
     *
     * @return null
     * @throws Throwable
     */
    public function make(string $name, array $parameters = [])
    {
        $object = Di::instance()->get($name);
        if (!$object) {
            // 兼容
            if (interface_exists($name)) {
                if (isset($this->dependencies[$name]) && class_exists($this->dependencies[$name])) {
                    $name = $this->dependencies[$name];
                } else {
                    return null;
                }
            }
            $parameters = array_values($parameters);
            $object = new $name(...$parameters);
        }

        return $object;
    }
}
