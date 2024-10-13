<?php
declare (strict_types=1);

namespace Kernel\Dns;

use Kernel\Component\Singleton;


class Dns
{
    use Singleton;

    /**
     * @param string $domain
     * @param string $type
     * @param array $nameservers
     * @return Entity\Dns[]
     */
    public function getRecord(string $domain, string $type = "A", array $nameservers = ["119.29.29.29"]): array
    {
        $resolver = new \Net_DNS2_Resolver(['nameservers' => $nameservers]);
        try {
            $result = $resolver->query($domain, $type);
            $arr = [];
            foreach ($result->answer as $item) {
                $arr[] = new Entity\Dns($item);
            }
            return $arr;
        } catch (\Throwable $e) {
            return [];
        }
    }
}