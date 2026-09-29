<?php
declare (strict_types=1);

namespace App\Controller\User\API\Config;


use App\Controller\User\Base;
use App\Interceptor\Merchant;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\Config as Model;
use App\Service\Common\Sms;
use App\Service\Common\Smtp;
use App\Validator\Common;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Theme;
use Kernel\Util\Date;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class, Merchant::class], type: Interceptor::API)]
class Config extends Base
{
    #[Inject]
    private Sms $sms;

    #[Inject]
    private Smtp $smtp;


    /**
     * @param string $key
     * @return Response
     * @throws RuntimeException
     */
    public function get(string $key): Response
    {
        $config = Model::query()->where("key", $key)->where("user_id", $this->getUser()->id)->first();
        $data = [];
        if ($config) {
            $data = json_decode((string)$config->value, true);
        }
        return $this->json(data: $data);
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

        //限制端口为标准 SMTP 端口、主机为合法主机名/IP，杜绝将测试功能用作任意端口探测(SSRF/端口扫描)
        $port = (int)$this->request->post("port");
        if (!in_array($port, [25, 465, 587, 2525], true)) {
            throw new JSONException("SMTP 端口不合法");
        }
        $host = (string)$this->request->post("host");
        if ($host === "" || strlen($host) > 255 || !preg_match('/^[A-Za-z0-9.\-_]+$/', $host)) {
            throw new JSONException("SMTP 主机不合法");
        }

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
     * @param string $key
     * @return Response
     * @throws JSONException
     */

    public function save(string $key): Response
    {
        if (!preg_match("/^(site|email|sms)$/", $key)) {
            throw new JSONException("索引未找到");
        }

        $config = \App\Model\Config::query()->where("key", $key)->where("user_id", $this->getUser()->id)->first();
        $post = $this->request->post(flags: Filter::NORMAL);
        (isset($post['pc_theme']) && $post['pc_theme'] === "") && ($post['pc_theme'] = "default");
        (isset($post['mobile_theme']) && $post['mobile_theme'] === "") && ($post['mobile_theme'] = "default");

        //主题标识白名单：该值后续会拼进模板/插件路径与 require()，禁止 ../ 路径穿越
        foreach (["pc_theme", "mobile_theme"] as $themeKey) {
            if (isset($post[$themeKey]) && !preg_match('/^[A-Za-z0-9_]+$/', (string)$post[$themeKey])) {
                throw new JSONException("主题标识错误");
            }
        }

        $keys = [
            "site" => ["网站设置", "icon-wangzhanshezhi", "/assets/admin/image/config/site.jpg"],
            "email" => ["邮件配置", "icon-a-kl_e10", "/assets/admin/image/config/email.jpg"],
            "sms" => ["短信配置", "icon-duanxinpeizhi", "/assets/admin/image/config/sms.jpg"],
        ];

        try {
            if (!$config) {
                $config = new \App\Model\Config();
                $config->key = $key;
                $config->title = $keys[$key][0];
                $config->icon = $keys[$key][1];
                $config->bg_url = $keys[$key][2];
                $config->user_id = $this->getUser()->id;
            }

            $config->value = json_encode($post, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $config->save();
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }
        return $this->response->json(message: "保存成功");
    }
}