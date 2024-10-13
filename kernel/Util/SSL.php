<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Component\Singleton;

class SSL
{
    use Singleton;

    /**
     * @param string $cert
     * @return array|null
     */
    public function getCertInfo(string $cert): ?array
    {
        $certResource = openssl_x509_read($cert);
        if ($certResource === false) {
            return null;
        }

        $certInfo = openssl_x509_parse($certResource);
        if ($certInfo === false) {
            return null;
        }

        if (!isset($certInfo['validTo_time_t']) || !isset($certInfo['extensions']['subjectAltName']) || !isset($certInfo['issuer']) || !is_array($certInfo['issuer'])) {
            return null;
        }


        return [
            "expire" => date('Y-m-d H:i:s', $certInfo['validTo_time_t']),
            "domain" => str_replace("DNS:", "", trim($certInfo['extensions']['subjectAltName'])),
            "issuer" => $certInfo['issuer']['CN']
        ];
    }
}