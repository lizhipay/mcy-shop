<?php
declare (strict_types=1);

namespace App\Controller\User\Pay;

use App\Controller\User\Base;
use App\Interceptor\PostDecrypt;
use App\Interceptor\Visitor;
use App\Interceptor\Waf;
use App\Model\Order;
use App\Model\PluginConfig;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\ViewException;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;

#[Interceptor(class: [PostDecrypt::class, Waf::class, Visitor::class], type: Interceptor::VIEW)]
class PayOrder extends Base
{

    #[Inject]
    private \App\Service\User\PayOrder $payOrder;


    /**
     * @return Response
     * @throws ViewException
     */
    public function pay(): Response
    {
        $tradeNo = $this->request->uriSuffix();

        if (!$tradeNo) {
            throw new ViewException("请勿随意修改URL");
        }

        $order = Order::query()->where("trade_no", $tradeNo)->first();
        if (!$order) {
            throw new ViewException("订单不存在");
        }

        if ($order->status != 3) {
            throw new ViewException("该订单已失效");
        }

        $payOrder = \App\Model\PayOrder::with(['option'])->where("order_id", $order->id)->first();

        if (!$payOrder) {
            throw new ViewException("订单不存在");
        }

        if ($payOrder->status != 1 && $payOrder->status != 0) {
            throw new ViewException("该订单已失效");
        }

        if (strtotime($payOrder->timeout) < time()) {
            throw new ViewException("该订单已超时");
        }

        $pay = $this->payOrder->getPay($payOrder->pay_id);

        /**
         * @var PluginConfig $config
         */
        $config = $pay->config;

        if (!$config) {
            throw new ViewException("支付配置文件不存在");
        }


        $data = [
            "order" => $payOrder->toArray(),
            "config" => $config->toArray()
        ];

        $payOrder->status = 1;
        $payOrder->save();
        switch ($payOrder->render_mode) {
            case \Kernel\Plugin\Const\Pay::RENDER_JUMP:
                return $this->response->redirect($payOrder->pay_url);
            case  \Kernel\Plugin\Const\Pay::RENDER_FORM_POST_SUBMIT:
                if (empty($payOrder->option)) {
                    throw new ViewException("参数丢失");
                }
                return $this->response->render(template: "User/Pay/Render/Submit.html", data: ["url" => $payOrder->pay_url, "option" => $payOrder->option->option]);
            case  \Kernel\Plugin\Const\Pay::RENDER_LOCAL_PLUGIN_VIEW:
                //实现插件视图渲染
                return $this->response->render(
                    template: "{$pay->code}.html",
                    title: $pay->name,
                    data: $data,
                    path: Plugin::instance()->getPayViewPath(
                        name: $config->plugin,
                        env: Usr::inst()->userToEnv($config->user_id)
                    )
                );
            case  \Kernel\Plugin\Const\Pay::RENDER_COMMON_ALIPAY_VIEW:
            case  \Kernel\Plugin\Const\Pay::RENDER_COMMON_WECHAT_VIEW:
            case  \Kernel\Plugin\Const\Pay::RENDER_COMMON_QQ_VIEW:
                return $this->response->redirect("/checkout?tradeNo=" . $order->trade_no);
            default:
                throw new ViewException("没有找到可处理的视图");
        }
    }

    /**
     * @return Response
     * @throws ViewException
     */
    public function sync(): Response
    {
        $tradeNo = $this->request->uriSuffix();
        if (!$tradeNo) {
            throw new ViewException("请勿随意修改URL");
        }
        return $this->response->redirect($this->payOrder->getSyncUrl($tradeNo));
    }
}