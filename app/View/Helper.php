<?php
declare (strict_types=1);

namespace App\View;

use App\Model\User;
use App\Service\Common\Config;
use Kernel\Annotation\Inject;
use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Context\Interface\Response;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Const\Plugin as PGN;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Context;
use Kernel\Util\Str;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Helper extends AbstractExtension
{

    use Singleton;

    #[Inject]
    private Config $config;


    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('js', [$this, 'loadJs']),
            new TwigFunction('css', [$this, 'loadCss']),
            new TwigFunction('icon', [$this, 'loadIcon']),
            new TwigFunction('ready', [$this, 'ready']),
            new TwigFunction('var', [$this, 'setScriptVar']),
            new TwigFunction('i18n', [$this, 'i18n']),
            new TwigFunction('hook', [$this, 'hook']),
            new TwigFunction('multi_hook', [$this, 'multiHook']),
            new TwigFunction('user_env', [$this, 'userEnv']),
            new TwigFunction('usr', [$this, 'usr']),
            new TwigFunction('env', [$this, 'env']),
            new TwigFunction('point', [$this, 'point']),
            new TwigFunction('ccy', [$this, 'currency']),
            new TwigFunction('plugin_backstage_route', [$this, 'getPluginBackstageRoute']),
            new TwigFunction('m_config', [$this, 'getMainConfig']),
        ];
    }


    /**
     * @param string $key
     * @return mixed
     */
    public function getMainConfig(string $key): mixed
    {
        return $this->config->getMainConfig($key);
    }

    /**
     * @param string $name
     * @param string $uri
     * @return string
     */
    public function getPluginBackstageRoute(string $name, string $uri = ""): string
    {
        $user = Context::get(User::class);
        return "/plugin/" . ($user ? "{$user->id}/" : "") . Str::camelToSnake($name) . "/" . trim($uri, "/");
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function currency(): string
    {
        return Di::instance()->make(Config::class)->getCurrency()->symbol;
    }


    /**
     * @param string $env
     * @param int $point
     * @param int $type
     * @param ...$arg
     * @return array|string|bool|Response
     * @throws \ReflectionException
     */
    public function hook(string $env, int $point, int $type = PGN::HOOK_TYPE_PAGE, ...$arg): array|string|bool|Response
    {
        return Plugin::instance()->hook($env, $point, $type, ...$arg);
    }

    /**
     * @param array $users
     * @param int $point
     * @param int $type
     * @param ...$arg
     * @return array|string|bool|Response
     * @throws \ReflectionException
     */
    public function multiHook(array $users, int $point, int $type = PGN::HOOK_TYPE_PAGE, ...$arg): array|string|bool|Response
    {
        return Plugin::instance()->multiHook($users, $point, $type, ...$arg);
    }

    /**
     * @param bool $forceSys
     * @return string
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function env(bool $forceSys = false): string
    {
        if ($forceSys) {
            return Usr::MAIN;
        }
        return Usr::inst()->getEnv();
    }

    /**
     * @param int|null $userId
     * @return string
     * @throws \ReflectionException
     */
    public function userEnv(?int $userId): string
    {
        return Usr::inst()->userToEnv($userId);
    }

    /**
     * @return string
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function usr(): string
    {
        return Usr::inst()->getUsr();
    }

    /**
     * @param string $name
     * @return int
     */
    public function point(string $name): int
    {
        return constant("Kernel\\Plugin\\Const\\Point::" . $name);
    }

    /**
     * 加载Css
     * @param array|string $resource
     * @param array|string|null $backup
     * @param bool $cdn
     * @return string
     */
    function loadCss(array|string $resource, array|string|null $backup = null, bool $cdn = true): string
    {
        if (App::$debug && $backup !== null) {
            $resource = $backup;
        }
        $res = '';
        $debugRandom = App::$debug ? "&debug=" . Str::generateRandStr(8) : "";
        $cdnSupport = $cdn ? 'class="cdn-support"' : '';
        if (is_array($resource)) {
            foreach ($resource as $item) {
                $res .= sprintf('<link rel="stylesheet" href="%s" ' . $cdnSupport . '>', $item . '?v=' . App::$version . $debugRandom);
            }
        } else {
            $res = sprintf('<link rel="stylesheet" href="%s" ' . $cdnSupport . '>', $resource . '?v=' . App::$version . $debugRandom);
        }
        return $res;
    }

    /**
     *  加载JS
     * @param array|string $resource
     * @param array|string|null $backup
     * @param bool $cdn
     * @return string
     */
    function loadJs(array|string $resource, array|string|null $backup = null, bool $cdn = true): string
    {
        if (App::$debug && $backup !== null) {
            $resource = $backup;
        }
        $res = '';
        $debugRandom = App::$debug ? "&debug=" . Str::generateRandStr(8) : "";
        $cdnSupport = $cdn ? ' class="cdn-support"' : '';
        if (is_array($resource)) {
            foreach ($resource as $item) {
                $res .= sprintf('<script src="%s" ' . $cdnSupport . '></script>', $item . (str_contains($item, "?") ? "&" : "?") . 'v=' . App::$version . $debugRandom);
            }
        } else {
            $res = sprintf('<script src="%s" ' . $cdnSupport . '></script>', $resource . (str_contains($resource, "?") ? "&" : "?") . 'v=' . App::$version . $debugRandom);
        }
        return $res;
    }


    /**
     * 加载ICON
     * @param string $icon
     * @param string ...$class
     * @return string
     */
    public function loadIcon(string $icon, string ...$class): string
    {
        return '<svg class="mcy-icon ' . (implode(" ", $class)) . '" aria-hidden="true"><use xlink:href="#' . $icon . '"></use></svg>';
    }


    /**
     *  加载并且执行JS
     * @param string $resource
     * @param array $variable
     * @return string
     */
    public function ready(string $resource, array $variable = []): string
    {
        $var = '';
        foreach ($variable as $key => $value) {
            $var .= "setVar('{$key}' , {$this->getValue($value)});";
        }
        return '<script>' . $var . 'ready("' . $resource . (str_contains($resource, "?") ? "&" : "?") . 'v=' . App::$version . (App::$debug ? "&debug=" . Str::generateRandStr(8) : '') . '");</script>';
    }

    /**
     * @param mixed $value
     * @return string|bool|null
     */
    private function getValue(mixed $value): string|bool|null
    {
        if (is_numeric($value) || is_bool($value)) {
            // 对于数字和布尔值，不添加双引号
            $value = var_export($value, true);
        } elseif (is_array($value)) {
            // 如果是数组，转换为JSON
            $value = json_encode($value);
        } else {
            // 对于字符串，进行转义并添加双引号
            $value = addslashes($value);
            $value = "\"$value\"";
        }
        return $value;
    }

    /**
     * @param array $vars
     * @return string
     */
    public function setScriptVar(array $vars): string
    {
        $str = "<script>";
        foreach ($vars as $name => $var) {
            $str .= "setVar(\"{$name}\",{$this->getValue($var)});";
        }
        return $str . "</script>";
    }

    /**
     * @param string $text
     * @return string
     * @throws \ReflectionException
     */
    public function i18n(mixed $text): string
    {
        return \Kernel\Language\Language::instance()->output((string)$text);
    }

    /**
     * 压缩CSS
     * @param string $css
     * @return string
     */
    public function compressCss(string $css): string
    {
        // 移除注释
        $css = preg_replace('!/\*.*?\*/!s', '', $css);
        // 移除空格和换行
        $css = preg_replace('/\s*([{}|:;,])\s+/', '$1', $css);
        // 压缩后续空格
        $css = preg_replace('/\s\s+(.*)/', '$1', $css);
        // 移除最后一个分号和空格
        $css = str_replace(';}', '}', $css);
        return trim($css);
    }
}