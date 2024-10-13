<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use App\Model\Order;
use App\Model\PayOrder;
use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Plugin\Handle\Pay as a;

class Pay
{

    use Singleton;

    /**
     * @param string $name
     * @param string $env
     * @param Order $order
     * @param PayOrder $payOrder
     * @param array $config
     * @param string $code
     * @param string $clientIp
     * @param string|null $amount
     * @param string|null $asyncUrl
     * @param string|null $syncUrl
     * @return Pay|null
     * @throws \ReflectionException
     */
    public function handle(string $name, string $env, Order $order, PayOrder $payOrder, array $config, string $code, string $clientIp, ?string $amount = null, ?string $asyncUrl = null, ?string $syncUrl = null): ?a
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return null;
        }

        if ($plugin->state['run'] != 1) {
            return null;
        }

        if (!isset($plugin->handle) || !isset($plugin->handle[a::class])) {
            return null;
        }

        $handle = $plugin->handle[a::class];

        if (!class_exists($handle)) {
            return null;
        }

        $obj = new $handle($plugin, $order, $payOrder, $config, $code, $clientIp, $amount, $asyncUrl, $syncUrl);
        Di::inst()->inject($obj);
        return $obj;
    }
}