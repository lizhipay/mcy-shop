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