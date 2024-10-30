<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Config;


use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use App\Model\Config as Model;
use App\Model\ManageLog;
use App\Model\Site;
use App\Service\Common\Sms;
use App\Service\Common\Smtp;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\App;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Date;
use Kernel\Util\File;
use Kernel\Util\Http;
use Kernel\Util\Ip;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Config extends Base
{
    #[Inject]
    private Sms $sms;


    #[Inject]
    private Smtp $smtp;


    #[Inject]
    private \App\Service\User\Site $site;


    /**
     * @param string $key
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    public function get(string $key): Response
    {
        $config = Model::query()->where("key", $key)->whereNull("user_id")->first();
        if (!$config) {
            throw new JSONException("配置不存在");
        }

        $data = json_decode((string)$config->value, true);

        return $this->json(data: $data);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, "email"]
    ])]
    public function smtpTest(): Response
    {
        $to = $this->request->post("email");

        if (!$this->smtp->send(
            to: $to,
            title: "测试发信",
            body: "这是一封来自异世界的邮件，发送时间是：" . Date::current(),
            config: $this->request->post()
        )) {
            throw new JSONException("邮件发送失败");
        }

        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException|JSONException
     */
    #[Validator([
        [Common::class, "phone"]
    ])]
    public function smsTest(): Response
    {
        try {
            $captcha = (string)mt_rand(100000, 999999);
            $config = $this->request->post();
            $platform = (int)$config['platform'];
            $templateCode = match ($platform) {
                \App\Const\Sms::PLATFORM_ALI => $config['ali_template_code'], //阿里云
                \App\Const\Sms::PLATFORM_TENCENT => $config['tencent_template_id'], //腾讯云
                \App\Const\Sms::PLATFORM_DXB => str_replace("{code}", $captcha, $config['dxb_template'])//短信宝
            };
            $var = match ($platform) {
                \App\Const\Sms::PLATFORM_ALI => ['code' => $captcha], //阿里云
                \App\Const\Sms::PLATFORM_TENCENT => [(string)$captcha], //腾讯云
                \App\Const\Sms::PLATFORM_DXB => [], //短信宝
            };
            $this->sms->send($config, $config['phone'], $templateCode, $var);
        } catch (\Throwable $e) {
            throw new JSONException($e->getMessage());
        }
        return $this->json();
    }

    /**
     * @param string $key
     * @return Response
     * @throws JSONException
     */
    public function save(string $key): Response
    {
        $config = \App\Model\Config::query()->where("key", $key)->whereNull("user_id")->first();
        if (!$config) {
            throw new JSONException("当前提交的配置不存在，无法保存");
        }
        $post = $this->request->post(flags: Filter::NORMAL);

        (isset($post['pc_theme']) && $post['pc_theme'] === "") && ($post['pc_theme'] = "default");
        (isset($post['mobile_theme']) && $post['mobile_theme'] === "") && ($post['mobile_theme'] = "default");


        try {
            if ($key == "subdomain") {

                if (!App::$cli) {
                    try {
                        $response = Http::make()->get($post['nginx_fpm_url'] . "/hello");
                        $contents = (array)json_decode((string)$response->getBody()->getContents());

                        if (!isset($contents['code'])) {
                            throw new JSONException("访问Hello时，返回值错误");
                        }
                    } catch (\Throwable $e) {
                        if (str_contains($e->getMessage(), "SSL_connect: Connection reset by peer in connection") && str_contains($e->getMessage(), ":443")) {
                            throw new JSONException("系统对FPM地址进行测试时，发现该地址出现了强制HTTPS跳转，请关闭[{$post['nginx_fpm_url']}]的HTTPS强制跳转在尝试！");
                        }

                        throw new JSONException("FPM地址有误，无法ping通，错误信息：" . $e->getMessage());
                    }
                }

                $list = Site::query()->where("type", 1)->get();
                $proxyPass = App::$cli ? "http://127.0.0.1:" . \Kernel\Util\Config::get("cli-server.port") : $post['nginx_fpm_url'];

                /**
                 * @var Site $item
                 */
                foreach ($list as $item) {
                    $nginxInfo = $this->site->getNginxInfo($item->host);
                    $nginxProxyConfig = $this->site->getNginxProxyConfig($nginxInfo, $proxyPass, $post['nginx_conf']);
                    if (!File::write($nginxInfo->conf, $nginxProxyConfig)) {
                        throw new JSONException("配置环境写入失败，请检查文件写入权限");
                    }
                }
            } elseif ($key == "site") {
                $post['ip_mode'] && Ip::setMode($post['ip_mode']);
            }
            $config->value = json_encode($post, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $config->save();
            ManageLog::add("修改了网站设置->[$config->title]");
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }

}