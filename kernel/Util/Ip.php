<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Context\App;
use Kernel\Context\Interface\Request;

class Ip
{

    public const MODE_FILE = BASE_PATH . "/runtime/ip.mode";

    public const IP_PROTOCOL_HEADER = ['XForwardedFor', 'XRealIp', 'ClientIp', 'XForwarded', 'XClusterClientIp', 'ForwardedFor', 'Forwarded', 'CfConnectingIp'];

    /**
     * @param Request $request
     * @return string|null
     */
    public static function get(Request $request): ?string
    {
        if (App::$install) {
            $mode = (File::read(self::MODE_FILE) ?: "auto");
            if ($mode != "auto") {
                $clientIp = $request->header($mode);
                $clientIp = $clientIp ? trim(explode(',', $clientIp)[0]) : null;
                if ($clientIp) {
                    return $clientIp;
                }
            }
        }
        return null;
    }


    /**
     * @param string $header
     * @return void
     */
    public static function setMode(string $header): void
    {
        File::write(self::MODE_FILE, $header);
    }
}