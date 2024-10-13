<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use Kernel\Annotation\Inject;
use Kernel\Context\App;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Const\Plugin as PGI;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use PHPMailer\PHPMailer\PHPMailer;

class Smtp implements \App\Service\Common\Smtp
{
    #[Inject]
    private \App\Service\Common\Config $config;

    /**
     * @param string $to
     * @param string $title
     * @param string $body
     * @param array $files
     * @param array $config
     * @return bool
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function send(string $to, string $title, string $body, array $files = [], array $config = []): bool
    {
        if (empty($config)) {
            $config = $this->config->getMainConfig("email");
        }

        $options = [
            "config" => $config,
            "to" => $to,
            "title" => $title,
            "body" => $body,
            "files" => $files
        ];

        $hook = Plugin::instance()->hook(App::env(), Point::SERVICE_SMTP_SEND_BEFORE, PGI::HOOK_TYPE_PAGE, $options);
        if (is_bool($hook)) return $hook;

        try {
            $mail = new PHPMailer();
            $mail->CharSet = 'UTF-8';
            $mail->IsSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            if ($config['secure'] != "none") {
                $mail->SMTPSecure = $config['secure'];
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            $mail->Host = $config['host'];
            $mail->Port = $config['port'];
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SetFrom($config['username'], $config['nickname']);
            $mail->AddAddress($to);
            $mail->Subject = $title;
            $mail->MsgHTML($body);
            $mail->Timeout = 10;
            $result = $mail->Send();
        } catch (\Exception $e) {
            $hook = Plugin::instance()->hook(App::env(), Point::SERVICE_SMTP_SEND_ERROR, PGI::HOOK_TYPE_PAGE, $options);
            if (is_bool($hook)) return $hook;
            return false;
        }

        if (!$result) {
            $hook = Plugin::instance()->hook(App::env(), Point::SERVICE_SMTP_SEND_ERROR, PGI::HOOK_TYPE_PAGE, $options);
            if (is_bool($hook)) return $hook;
            return false;
        }

        $hook = Plugin::instance()->hook(App::env(), Point::SERVICE_SMTP_SEND_SUCCESS, PGI::HOOK_TYPE_PAGE, $options);
        if (is_bool($hook)) return $hook;
        return true;
    }
}