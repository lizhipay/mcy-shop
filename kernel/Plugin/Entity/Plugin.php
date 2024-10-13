<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

use App\Model\PluginConfig;
use Kernel\Container\Memory;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Usr;
use Kernel\Template\Template;
use Kernel\Util\Arr;
use Kernel\Util\Str;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Plugin
{
    public string $id;
    public string $name;
    public array $info;
    public string $submit;
    public array $state;
    public array $route;
    public string $routeUrl;
    public array $command;
    public array $menu;
    public string $path;
    public string $staticPath;
    public array $handle;
    public string $env;
    public string $icon;
    public string $handleSubmit;
    public array $payCode;
    public array $language;
    public array $config;
    public array $systemConfig;
    public string $uid;
    public array $theme;

    /**
     * @param string $name
     * @param array $info
     * @param string $submit
     * @param array $route
     * @param array $command
     * @param array $state
     * @param string $path
     * @param string $staticPath
     * @param array $menu
     * @param array $handle
     * @param string $env
     * @param string $handleSubmit
     * @param array $payCode
     * @param array $languages
     * @param array $theme
     * @throws \ReflectionException
     */
    public function __construct(string $name, array $info, string $submit, array $route, array $command, array $state, string $path, string $staticPath, array $menu, array $handle, string $env, string $handleSubmit, array $payCode, array $languages, array $theme)
    {
        $this->id = $name;
        $this->name = $name;
        $this->submit = $submit;
        $this->info = $info;
        $this->route = $route;
        $this->command = $command;
        $this->state = $state;
        $this->path = $path;
        $this->staticPath = $staticPath;
        $this->menu = $menu;
        $this->handle = $handle;
        $this->env = $env;
        $this->icon = $env . "/" . $name . "/Icon.ico";
        $this->handleSubmit = $handleSubmit;
        $this->payCode = $payCode;
        $this->language = $languages;
        $this->config = $this->getConfig();
        $this->systemConfig = $this->getSystemConfig();
        $this->uid = Usr::inst()->envToUsr($env);
        $this->routeUrl = "/plugin/" . ($this->uid != "*" ? "{$this->uid}/" : "") . Str::camelToSnake($name);
        $this->theme = $theme;
    }

    /**
     * @param string|null $key
     * @param bool $physical
     * @return mixed
     * @throws \ReflectionException
     */
    public function getConfig(?string $key = null, bool $physical = false): mixed
    {
        $cacheKey = "plugin_config_" . md5($this->env . $this->name);
        if (!$physical && Memory::instance()->has($cacheKey)) {
            return Arr::get(Memory::instance()->get($cacheKey), $key);
        }
        $config = \Kernel\Plugin\Plugin::instance()->getConfig($this->name, $this->env);
        !$physical && Memory::instance()->set($cacheKey, $config);

        return Arr::get($config, $key);
    }

    /**
     * @param string|null $key
     * @param bool $physical
     * @return mixed
     * @throws \ReflectionException
     */
    public function getSystemConfig(?string $key = null, bool $physical = false): mixed
    {
        $cacheKey = "plugin_sys_config_" . md5($this->env . $this->name);
        if (!$physical && Memory::instance()->has($cacheKey)) {
            return Arr::get(Memory::instance()->get($cacheKey), $key);
        }
        $config = \Kernel\Plugin\Plugin::instance()->getSystemConfig($this->name, $this->env);
        !$physical && Memory::instance()->set($cacheKey, $config);
        return Arr::get($config, $key);
    }

    /**
     * @param string $handle
     * @param bool $isGetConfig
     * @return array
     */
    public function getHandleConfigList(string $handle, bool $isGetConfig = false): array
    {
        $columns = ["id", "name"];
        $isGetConfig && $columns[] = "config";
        $items = PluginConfig::where("plugin", $this->name)->where("handle", $handle);
        if ($this->uid == "*") {
            $items = $items->whereNull("user_id");
        } else {
            $items = $items->where("user_id", $this->uid);
        }
        $list = $items->get($columns)->toArray();
        foreach ($list as &$item) {
            if (!empty($item['config'])) {
                $item['config'] = is_array($item['config']) ? $item['config'] : [];
            }
        }
        return $list;
    }

    /**
     * 加载模板
     * @param string $template
     * @param array $data
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function view(string $template, array $data = []): string
    {
        return Template::instance()->load($template, $data, $this->path . "/View/", true);
    }

    /**
     * @param string $message
     * @param bool $important
     * @param bool $physical
     * @return void
     * @throws \ReflectionException
     */
    public function log(mixed $message, bool $important = false, bool $physical = false): void
    {
        if ($this->getSystemConfig("log", $physical) != 1 && !$important) {
            return;
        }

        $text = "";
        if (is_string($message) || is_bool($message) || is_numeric($message) || is_double($message) || is_float($message) || is_integer($message)) {
            $text = (string)$message;
        } elseif (is_array($message) || is_object($message)) {
            $text = PHP_EOL . json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        $file = $this->path . "/Runtime";
        $contents = "[" . date("Y-m-d H:i:s", time()) . "]:" . $text . PHP_EOL;
        file_put_contents($file, $contents, FILE_APPEND);
    }
}