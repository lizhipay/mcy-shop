<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Context\App;
use Kernel\Context\Interface\Request;

class Ip
{


    public const IP_PROTOCOL_HEADER = ['XRealIp', 'XForwardedFor', 'ClientIp', 'XForwarded', 'XClusterClientIp', 'ForwardedFor', 'Forwarded', 'CfConnectingIp'];

    /**
     * @param Request $request
     * @return string|null
     */
    public static function get(Request $request): ?string
    {
        if (App::$install) {
            $secure = (int)(File::read(BASE_PATH . "/runtime/secure.tunnel") ?: 0);
            if ($secure > 0 && isset(self::IP_PROTOCOL_HEADER[$secure - 1])) {
                $clientIp = $request->header(self::IP_PROTOCOL_HEADER[$secure - 1]);
                $clientIp = $clientIp ? trim(explode(',', $clientIp)[0]) : null;
                if ($clientIp) {
                    return $clientIp;
                }
            }
        }
        return null;
    }
}