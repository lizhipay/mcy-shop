<?php
declare (strict_types=1);

namespace Kernel\Plugin\Abstract;

use App\Model\Order;
use App\Model\PayOrder;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Entity\Plugin;
use Kernel\Util\Date;

abstract class Pay implements \Kernel\Plugin\Handle\Pay
{
    #[Inject]
    protected Request $request;

    #[Inject]
    protected Response $response;

    #[Inject]
    protected \App\Service\User\Order $orderService;


    //商品订单信息
    protected Order $order;
    //支付订单信息
    protected PayOrder $payOrder;
    //插件信息
    protected Plugin $plugin;
    //支付配置信息
    protected array $config;
    //支付通道CODE代码
    protected string $code;
    //客户IP地址
    protected string $clientIp;
    //支付金额
    protected ?string $amount;
    //异步地址
    protected ?string $asyncUrl;
    //同步地址
    protected ?string $syncUrl;


    /**
     * @param Plugin $plugin
     * @param Order $order
     * @param PayOrder $payOrder
     * @param array $config
     * @param string $code
     * @param string $clientIp
     * @param string|null $amount
     * @param string|null $asyncUrl
     * @param string|null $syncUrl
     * @throws \ReflectionException
     */
    public function __construct(Plugin $plugin, Order $order, PayOrder $payOrder, array $config, string $code, string $clientIp, ?string $amount = null, ?string $asyncUrl = null, ?string $syncUrl = null)
    {
        Di::inst()->inject($this);
        $this->order = $order;
        $this->plugin = $plugin;
        $this->config = $config;
        $this->clientIp = $clientIp;
        $this->amount = $amount;
        $this->asyncUrl = $asyncUrl;
        $this->syncUrl = $syncUrl;
        $this->code = $code;
        $this->payOrder = $payOrder;
    }

    /**
     * @return void
     */
    public function successful(): void
    {
        $this->orderService->deliver($this->order, $this->request->clientIp());
        $this->payOrder->status = 2;
        $this->payOrder->pay_time = Date::current();
        $this->payOrder->save();
    }


    /**
     * @return Response
     */
    public function sync(): Response
    {
        return $this->response;
    }
}