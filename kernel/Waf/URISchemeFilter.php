<?php
declare (strict_types=1);

namespace Kernel\Waf;

use App\Service\Common\Config;
use Kernel\Annotation\Inject;
use Kernel\Component\Make;
use Kernel\Context\App;
use Kernel\Util\Arr;

class URISchemeFilter extends \HTMLPurifier_URIFilter
{

    use Make;

    /**
     * @var string
     */
    public $name = 'URISchemeFilter';


    /**
     * @var array|string[]
     */
    public array $whitelist = [
        "*.bilibili.com",
        "*.youtube.com"
    ];

    #[Inject]
    protected Config $config;

    /**
     * @param $uri
     * @param $config
     * @param $context
     * @return bool
     */
    public function filter(&$uri, $config, $context): bool
    {
        if (is_null($uri->host)) {
            return true;
        }

        $whitelist = $this->whitelist;

        if (App::$install) {
            $cfg = $this->config->getMainConfig("waf");
            if (isset($cfg['uri_scheme_filter_open']) && $cfg['uri_scheme_filter_open'] == 1) {
                return true;
            }
            if (isset($cfg['uri_scheme_filter_whitelist'])) {
                $whitelist = Arr::strToList($cfg['uri_scheme_filter_whitelist'], "\n");
            }
        }
        
        foreach ($whitelist as $pattern) {
            $pattern = str_replace('.', '\.', $pattern);
            $pattern = str_replace('*', '.*', $pattern);
            if (preg_match('/^' . $pattern . '$/i', $uri->host)) {
                return true;
            }
        }
        return false;
    }
}