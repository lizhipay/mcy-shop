<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use GuzzleHttp\Exception\GuzzleException;
use Kernel\Annotation\Inject;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Http;
use Kernel\Util\Verify;
use Mrgoon\AliSms\AliSms;

class Sms implements \App\Service\Common\Sms
{

    #[Inject]
    private AliSms $aliSms;


    /**
     * @param array $config
     * @param string $phone
     * @param string $templateCode
     * @param array $var
     * @return void
     * @throws RuntimeException
     */
    private function sendAli(array $config, string $phone, string $templateCode, array $var = []): void
    {
        if (Verify::isInternationalMobile($phone)) {
            $phone = ltrim($phone, "+");
        }
        //阿里云
        $cfg = [
            'access_key' => $config['ali_access_key_id'],
            'access_secret' => $config['ali_access_key_secret'],
            'sign_name' => $config['ali_sign_name']
        ];
        $response = $this->aliSms->sendSms($phone, $templateCode, $var, $cfg);
        if ($response->Message != "OK") {
            throw new RuntimeException($response->Message);
        }
    }

    /**
     * @param array $config
     * @param string $phone
     * @param string $templateCode
     * @param array $var
     * @return void
     * @throws GuzzleException
     * @throws RuntimeException
     */
    private function sendTencent(array $config, string $phone, string $templateCode, array $var = []): void
    {
        if (Verify::isChinaMobile($phone)) {
            $phone = "+86" . $phone;
        }
        $host = "sms.tencentcloudapi.com";
        $param = [
            "Nonce" => 11886,
            "Timestamp" => time(),
            "Region" => $config['tencent_region'],
            "SecretId" => $config['tencent_secret_id'],
            "Version" => "2021-01-11",
            "Action" => "SendSms",
            "SmsSdkAppId" => $config['tencent_sdk_app_id'],
            "SignName" => $config['tencent_sign_name'],
            "TemplateId" => $templateCode,
            "PhoneNumberSet.0" => $phone
        ];
        foreach ($var as $index => $item) {
            $param["TemplateParamSet." . $index] = $item;
        }
        ksort($param);
        $signStr = "GET" . $host . "/?";
        foreach ($param as $key => $value) {
            $signStr = $signStr . $key . "=" . $value . "&";
        }
        $signStr = substr($signStr, 0, -1);
        $signature = base64_encode(hash_hmac("sha1", $signStr, $config['tencent_secret_key'], true));
        $param["Signature"] = $signature;
        $paramStr = "";
        foreach ($param as $key => $value) {
            $paramStr = $paramStr . $key . "=" . urlencode((string)$value) . "&";
        }
        $paramStr = substr($paramStr, 0, -1);
        $response = Http::make()->get("https://" . $host . "/?{$paramStr}");
        $json = json_decode((string)$response->getBody()->getContents(), true);
        if ((string)$json['Response']['SendStatusSet'][0]['Code'] != "Ok") {
            throw new RuntimeException("短信发送失败");
        }
    }

    /**
     * @param array $config
     * @param string $phone
     * @param string $templateCode
     * @return void
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function sendDxb(array $config, string $phone, string $templateCode): void
    {
        $pass = md5((string)$config['dxb_password']);
        $templateCode = urlencode($templateCode);
        $url = "https://api.smsbao.com/sms?u={$config['dxb_username']}&p={$pass}&m={$phone}&c={$templateCode}";
        if (Verify::isInternationalMobile($phone)) {
            $phone = urlencode($phone);
            $url = "https://api.smsbao.com/wsms?u={$config['dxb_username']}&p={$pass}&m={$phone}&c={$templateCode}";
        }
        $response = Http::make()->get($url);
        $contents = $response->getBody()->getContents();

        if ($contents != "0") {
            throw new RuntimeException("短信发送失败");
        }
    }

    /**
     * @param array $config
     * @param string $phone
     * @param string $templateCode
     * @param array $var
     * @return void
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function send(array $config, string $phone, string $templateCode, array $var = []): void
    {
        if (!Verify::isInternationalMobile($phone) && !Verify::isChinaMobile($phone)) {
            throw new RuntimeException("手机号格式错误");
        }

        $platform = (int)$config['platform'];
        switch ($platform) {
            case \App\Const\Sms::PLATFORM_ALI:
                $this->sendAli($config, $phone, $templateCode, $var);
                break;
            case \App\Const\Sms::PLATFORM_TENCENT:
                $this->sendTencent($config, $phone, $templateCode, $var);
                break;
            case \App\Const\Sms::PLATFORM_DXB:
                $this->sendDxb($config, $phone, $templateCode);
                break;
            default:
                throw new RuntimeException("暂不支持该平台");
        }
    }
}