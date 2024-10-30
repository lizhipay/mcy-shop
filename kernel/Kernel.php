<?php
declare (strict_types=1);

use Kernel\Context\App;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Config;
use Kernel\Util\Date;

ini_set('memory_limit', '1G');

//set timezone
date_default_timezone_set("Asia/Shanghai");

//抑制错误
//error_reporting(0);

//const
define("BASE_PATH", substr(rtrim(__DIR__, "/"), 0, -7) . "/");

//autoload
require(BASE_PATH . 'vendor/autoload.php');
//store
require(BASE_PATH . "kernel/Plugin/Store.php");

//APP
App::$startTime = Date::timestamp();
App::$cli = (php_sapi_name() == "cli");
App::$database = Config::get("database");
App::$session = Config::get("session");
App::$version = Config::get("app")['version'];
App::$opcache = extension_loaded("Zend OPcache") || extension_loaded("opcache");
App::$install = file_exists(BASE_PATH . "/kernel/Install/Lock");
App::$lock = App::$install ? (string)file_get_contents(BASE_PATH . "/kernel/Install/Lock") : "";
App::$language = Config::get("language");
App::$mode = (isset($argv[1]) && $argv[1] === "dev") ? "dev" : "service";
App::$debug = App::$mode == "service" ? Config::get("app")['debug'] : true;

/** @noinspection PhpUnhandledExceptionInspection */
Plugin::instance()->hook(Usr::MAIN, Point::KERNEL_INIT_BEFORE);

App::route();
App::command();
App::container();

//sapi
if (App::$cli) {
    require("CLI.php");
} else {
    require("FPM.php");
}