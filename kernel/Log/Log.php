<?php
declare (strict_types=1);

namespace Kernel\Log;

use Kernel\Component\Singleton;
use Kernel\Context\App;
use Kernel\Log\Const\Color;
use Kernel\Util\Config;

class Log
{
    use Singleton;

    /**
     * @var array
     */
    private array $config = [
        "error" => BASE_PATH . '/runtime/error.log',
        "update" => BASE_PATH . '/runtime/update.log',
        "info" => BASE_PATH . '/runtime/info.log',
        "debug" => BASE_PATH . '/runtime/debug.log'
    ];

    public function __construct()
    {
        $this->config = array_merge($this->config, Config::get('log'));
    }


    /**
     * @param string $message
     * @param int $color
     * @param bool $bold
     * @return void
     */
    public function stdout(string $message, int $color = Color::BLUE, bool $bold = false): void
    {
        $time = "[" . date("H:i:s", time()) . "]:";
        echo "\033[0;36m{$time}\033[0m\033[" . (int)$bold . ";{$color}m{$message}\033[0m\n";
    }

    /**
     * @param string $type
     * @return void
     */
    public function clear(string $type = "error"): void
    {
        file_put_contents($this->config[$type], "");
    }

    /**
     * @param string $message
     * @return void
     */
    public function error(string $message): void
    {
        if (App::$debug || \Kernel\Context\App::$isCommand) {
            $this->stdout($message, Color::RED, true);
        }
        $this->write($message, $this->config['error']);
    }

    /**
     * @param string $message
     * @return void
     */
    public function update(string $message): void
    {
        if (\Kernel\Context\App::$isCommand) {
            $this->stdout($message, Color::GREEN, true);
        }
        $this->write($message, $this->config['update']);
    }


    /**
     * @param string $message
     * @return void
     */
    public function info(string $message): void
    {
        if (App::$debug) {
            $this->stdout($message, Color::BLUE, true);
        }
        $this->write($message, $this->config['info']);
    }


    /**
     * @param string $message
     * @return void
     */
    public function debug(mixed $message): void
    {
        $text = "";
        if (is_string($message) || is_bool($message) || is_numeric($message) || is_double($message) || is_float($message) || is_integer($message)) {
            $text = (string)$message;
        } elseif (is_array($message) || is_object($message)) {
            $text = PHP_EOL . json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        if (App::$cli) {
            $this->stdout($text, Color::YELLOW, true);
        }

        $this->write($text, $this->config['debug']);
    }


    /**
     * @param string $message
     * @param string $file
     * @return void
     */
    private function write(string $message, string $file): void
    {
        file_put_contents($file, "[" . date("Y-m-d H:i:s", time()) . "]:" . $message . PHP_EOL, FILE_APPEND);
    }
}