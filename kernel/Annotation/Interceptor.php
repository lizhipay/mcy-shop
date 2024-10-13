<?php
declare(strict_types=1);

namespace Kernel\Annotation;


use Kernel\Annotation\Interface\Interceptor as InterceptorInterface;
use Kernel\Container\Di;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Context;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Interceptor
{
    const API = 0x1;
    const VIEW = 0x2;

    /**
     * Interceptor constructor.
     * @param string|array $class
     * @throws RuntimeException|\ReflectionException
     */
    public function __construct(mixed $class, int $type = self::VIEW)
    {
        if (is_array($class)) {
            foreach ($class as $c) {
                $this->run($c, $type);
            }
            return;
        }
        $this->run($class, $type);
    }

    /**
     * @param string $class
     * @param int $type
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    private function run(string $class, int $type): void
    {
        $di = Di::instance();
        if ($di->has($class)) {
            $var = $di->get($class);
        } else {
            $var = new $class();
            $di->set($class, $var);
        }

        if ($var instanceof InterceptorInterface) {
            $di->inject($var);
            $response = $var->handle(Context::get(Request::class), Context::get(Response::class), $type);
            Context::set(Response::class, $response);
            return;
        }

        throw new RuntimeException("interceptor not found");
    }

}