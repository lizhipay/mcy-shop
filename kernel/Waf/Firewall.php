<?php
declare (strict_types=1);

namespace Kernel\Waf;

use Kernel\Component\Singleton;
use Kernel\Context\Interface\Request;
use Kernel\Util\Context;

class Firewall
{

    use Singleton;

    /**
     * 防火墙规则列表
     * @var array
     */
    private array $rule = [];


    /**
     * @var \HTMLPurifier
     */
    private \HTMLPurifier $HTMLPurifier;

    /**
     * @throws \ReflectionException
     * @throws \HTMLPurifier_Exception
     */
    public function __construct()
    {
        $path = BASE_PATH . "/kernel/Waf/Rule";
        $this->rule["POST"] = json_decode(file_get_contents($path . "/post.json"), true);
        $this->rule["URL"] = json_decode(file_get_contents($path . "/url.json"), true);
        $this->rule["ARG"] = json_decode(file_get_contents($path . "/args.json"), true);
        $this->rule["COOKIE"] = json_decode(file_get_contents($path . "/cookie.json"), true);
        $this->rule["UA"] = json_decode(file_get_contents($path . "/ua.json"), true);
        $this->HTMLPurifierInit();
    }

    /**
     * @return void
     * @throws \HTMLPurifier_Exception
     * @throws \ReflectionException
     */
    private function HTMLPurifierInit(): void
    {
        $config = \HTMLPurifier_Config::createDefault();
        // 自定义 HTML 定义
        $config->set('HTML.DefinitionID', 'firewall.html');
        $config->set('HTML.DefinitionRev', 15);
        $config->set('Filter.Custom', [IgnoreStyleTagFilter::make()]);

        $config->getDefinition('URI')->addFilter(URISchemeFilter::make(), $config);

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addElement(
                'video',
                'Block',
                'Flow',
                'Common',
                array(
                    'poster' => 'URI',
                    'controls' => 'Bool#controls',
                    'width' => 'Text',
                    'height' => 'Text',
                    'src' => 'URI'
                )
            );
            $def->addElement(
                'source',
                'Block',
                'Flow',
                'Common',
                array(
                    'src*' => 'URI',
                    'type' => 'Text'
                )
            );
            $def->addElement(
                'marquee',
                'Block',
                'Flow',
                'Common',
                array(
                    'behavior' => 'Enum#scroll,slide,alternate',
                    'direction' => 'Enum#left,right,up,down',
                    'scrollamount' => 'Number',
                    'scrolldelay' => 'Number',
                    'loop' => 'Number',
                    'bgcolor' => 'Text',
                    'width' => 'Text',
                    'height' => 'Text',
                    'style' => 'Text'
                )
            );
            $def->addElement(
                'iframe',
                'Block',
                'Flow',
                'Common',
                array(
                    'src*' => 'URI',
                    'scrolling' => 'Enum#yes,no,auto',
                    'border' => 'Text',
                    'frameborder' => 'Text',
                    'framespacing' => 'Text',
                    'allowfullscreen' => 'Bool',
                    'sandbox' => 'Text',
                    'width' => 'Text',
                    'height' => 'Text',
                    'allow' => 'Text'
                )
            );
            $def->addAttribute('div', 'data-w-e-type', 'Text');
            $def->addAttribute('div', 'data-w-e-is-void', 'Bool');
            $def->addAttribute('a', 'target', 'Text');
            $def->addAttribute('img', 'width', 'Text');
            $def->addAttribute('img', 'height', 'Text');
        }

        $this->HTMLPurifier = new \HTMLPurifier($config);
    }


    /**
     * @param callable $callable
     * @return void
     */
    public function check(callable $callable): void
    {
        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);


        //GET过滤
        $getPara = urldecode(http_build_query($request->get()));
        foreach ($this->rule["ARG"] as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", $getPara)) {
                $callable($value);
                return;
            }
        }

        foreach ($this->rule["URL"] as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", $getPara)) {
                $callable($value);
                return;
            }
        }

        //POST过滤
        $postPara = urldecode(http_build_query($request->post()));
        foreach ($this->rule["POST"] as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", $postPara)) {
                $callable($value);
                return;
            }
        }

        //COOKIE过滤
        $cookiePara = urldecode(http_build_query($request->cookie()));
        foreach ($this->rule["COOKIE"] as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", $cookiePara)) {
                $callable($value);
                return;
            }
        }

        //header
        $headerPara = urldecode(http_build_query($request->header()));
        foreach ($this->rule["UA"] as $key => $value) {
            if (preg_match("#" . $value[1] . "#i", $headerPara)) {
                $callable($value);
                return;
            }
        }
    }


    /**
     * @param mixed $input
     * @return mixed
     */
    public function xssKiller(mixed $input): mixed
    {
        if (is_array($input)) {
            $cleanedArray = [];
            foreach ($input as $key => $value) {
                if (is_string($value)) {
                    $cleanedArray[$key] = $this->HTMLPurifier->purify(urldecode(str_replace("+", "%2B", $value)));
                } elseif (is_array($value)) {
                    $cleanedArray[$key] = $this->xssKiller($value);
                } else {
                    $cleanedArray[$key] = $value;
                }
            }
            return $cleanedArray;
        } elseif (is_string($input)) {
            return $this->HTMLPurifier->purify(urldecode(str_replace("+", "%2B", $input)));
        } else {
            return $input;
        }
    }


    /**
     * @param mixed $input
     * @param int $flags
     * @return mixed
     */
    public function filterContent(mixed $input, int $flags): mixed
    {
        if (is_null($input)) {
            return null;
        }

        if (is_array($input)) {
            $cleanedArray = [];
            foreach ($input as $key => $value) {
                if (is_string($value)) {
                    $cleanedArray[$key] = $this->filter($value, $flags);
                } elseif (is_array($value)) {
                    $cleanedArray[$key] = $this->filterContent($value, $flags);
                } else {
                    $cleanedArray[$key] = $value;
                }
            }
            return $cleanedArray;
        } else {
            return $this->filter($input, $flags);
        }
    }


    /**
     * @param mixed $content
     * @param int $flags
     * @return mixed
     */
    public function filter(mixed $content, int $flags): mixed
    {
        if (is_string($content)) {
            $content = trim($content);
            if ($flags & Filter::STRING_UNSIGNED) {
                $content = htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8');
            }
        }
        if ($flags & Filter::INTEGER) {
            $content = (int)$content;
        }
        if ($flags & Filter::FLOAT) {
            $content = (float)$content;
        }
        if ($flags & Filter::BOOLEAN) {
            $content = (bool)$content;
        }
        return $content;
    }
}