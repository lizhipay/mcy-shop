<?php
declare (strict_types=1);

namespace Kernel\Template;

use App\View\Helper;
use Kernel\Component\Singleton;
use Kernel\Context\App;
use Kernel\Exception\NotFoundException;
use Kernel\Log\Log;
use Kernel\Plugin\Const\Plugin as PGC;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class Template
{

    use Singleton;


    private array $caches = [];


    /**
     * @param string $template
     * @param array $data
     * @param string|array $path
     * @param bool $safety
     * @return string
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \ReflectionException
     */
    public function load(string $template, array $data = [], string|array $path = BASE_PATH . '/app/View', bool $safety = false): string
    {
        $themeHelper = $data['__theme_helper_class'] ?? null;

        $data['app'] = [
            'version' => App::$version,
            'debug' => App::$debug,
            'cli' => App::$cli
        ];

        is_array($path) ? ($cacheKey = md5(implode(".", $path))) : ($cacheKey = md5($path));

        if (!isset($this->caches[$cacheKey])) {
            $loader = new FilesystemLoader($path);
            $twig = new Environment($loader, [
                'cache' => BASE_PATH . '/runtime/view/cache',
                'debug' => App::$debug
            ]);
            $twig->addExtension(Helper::inst());
            $twig->addExtension(\App\View\Admin\Helper::inst());
            $twig->addExtension(\App\View\User\Helper::inst());

            if ($themeHelper && class_exists($themeHelper)) {
                $twig->addExtension($themeHelper::inst());
            }
            $this->caches[$cacheKey] = $twig;
        } else {
            $twig = $this->caches[$cacheKey];
        }

        $env = App::env();
        !$safety && Plugin::instance()->unsafeHook($env, Point::TEMPLATE_COMPILE_BEFORE, PGC::HOOK_TYPE_PAGE, $data, $twig);
        $template = $twig->load($template);
        $html = $template->render($data);

        if (!$safety) {
            $hook = Plugin::instance()->unsafeHook($env, Point::TEMPLATE_COMPILE_AFTER, PGC::HOOK_TYPE_PAGE, $html, $data);
            if (is_string($hook) && $hook != "") {
                $html = $hook;
            }
        }
        return $html;
    }


    /**
     * @param \Throwable $e
     * @return string
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError|\ReflectionException
     */
    public function error(\Throwable $e): string
    {
        Log::inst()->error($e->getFile() . ":" . $e->getLine() . ' ' . $e->getMessage());
        if (!App::$debug) {
            return "<b style='color: #ff8b39;'>):</b> <span style='color: #387eee;font-weight: bold;font-family: 微软雅黑,serif;'>发生了致命错误，当前版本：" . App::$version . "，错误日志已经记录在：<b style='color: green;'>/runtime/error.log</b>，请将日志提交给维护人员。</span>";
        }
        return $this->load("Runtime.html", ["error" => $e]);
    }


    /**
     * @param string $message
     * @param string|null $url
     * @param int $time
     * @return string
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError|\ReflectionException
     */
    public function redirect(string $message, ?string $url = null, int $time = 0): string
    {
        return $this->load("302.html", ["url" => $url, "time" => $time, "message" => $message]);
    }
}