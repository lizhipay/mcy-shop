<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use App\Entity\Store\Authentication;
use App\Entity\Store\UpdateLog;
use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Const\Plugin as PGN;
use Kernel\Plugin\Entity\HookInfo;
use Kernel\Plugin\Entity\Plugin as Entity;
use Kernel\Plugin\Handle\Database;
use Kernel\Util\Aes;
use Kernel\Util\File;
use Symfony\Component\Finder\Finder;


class Plugin
{
    use Singleton;

    /**
     * @var array
     */
    private array $runtime = [];


    /**
     * @var array
     */
    private array $startups = [];

    /**
     * @var array
     */
    private array $languages = [];


    /**
     * @var array
     */
    private array $config = [];

    /**
     * @var array
     */
    private array $systemConfig = [];


    /**
     * @return string
     */
    public function getHwId(): string
    {
        return @cd4d898edaf466b53198e8640e426c2f();
    }

    /**
     * @param string $name
     * @param string $env
     * @return Entity|null
     * @throws \ReflectionException
     */
    public function getPlugin(string $name, string $env = "/app/Plugin"): ?Entity
    {
        $path = BASE_PATH . $env . "/{$name}";
        list($info, $submit, $route, $command, $menu, $handle, $handleSubmit, $payCode, $theme) = [$path . "/Config/Info.php", $path . "/Config/Submit.js", $path . "/Config/Route.php", $path . "/Config/Command.php", $path . "/Config/Menu.php", $path . "/Config/Handle.php", $path . "/Config/Handle.js", $path . "/Config/Pay.php", $path . "/Config/Theme.php"];

        if (!file_exists($info)) {
            return null;
        }

        $state = $this->getState($name, $env);
        $languages = Language::inst()->list($name, $env);

        //插件信息
        return new Entity(
            $name,
            (array)require($info),
            file_exists($submit) ? File::read($submit) : '',
            file_exists($route) ? (array)require($route) : [],
            file_exists($command) ? (array)require($command) : [],
            $state,
            realpath($path),
            $env . "/{$name}",
            file_exists($menu) ? (array)require($menu) : [],
            file_exists($handle) ? (array)require($handle) : [],
            $env,
            file_exists($handleSubmit) ? File::read($handleSubmit) : '',
            file_exists($payCode) ? (array)require($payCode) : [],
            $languages,
            file_exists($theme) ? (array)require($theme) : []
        );
    }


    /**
     * @param string $env
     * @return array
     */
    public function getPluginVersionKeys(string $env = "/app/Plugin"): array
    {
        $baseDir = BASE_PATH . $env;
        if (!is_dir($baseDir)) {
            return [];
        }

        $finder = Finder::create()
            ->in($baseDir)
            ->depth(0)
            ->ignoreUnreadableDirs(true)
            ->directories();

        $plugins = [];
        foreach ($finder as $file) {
            $key = $file->getFilename();
            $info = $baseDir . "/{$key}/Config/Info.php";
            if (file_exists($info) && is_array($ifo = require($info))) {
                $plugins[$key] = $ifo['version'] ?? "1.0.0";
            }
        }

        return $plugins;
    }

    /**
     * @param string $name
     * @param string $env
     * @return bool
     */
    public function isRunning(string $name, string $env = "/app/Plugin"): bool
    {
        return $this->getState($name, $env)['run'] == 1;
    }

    /**
     * @param string $name
     * @param string $env
     * @return bool
     */
    public function exist(string $name, string $env = "/app/Plugin"): bool
    {
        return file_exists(BASE_PATH . $env . "/{$name}/Config/Info.php");
    }


    /**
     * @param string $env
     * @param int $id
     * @param string $key
     * @return void
     * @throws RuntimeException
     */
    public function setStoreUser(int $id, string $key, string $env): void
    {
        File::write(BASE_PATH . "/config/store/" . md5($env), $this->encrypt(["id" => $id, "key" => $key]));
    }

    /**
     * @param string $env
     * @return Authentication|null
     */
    public function getStoreUser(string $env): Authentication|null
    {
        $store = File::read(BASE_PATH . "/config/store/" . md5($env), function (string $contents) {
            return $this->decrypt($contents);
        });
        if (isset($store['id']) && isset($store['key'])) {
            return new Authentication((int)$store['id'], $store['key'], $env != "main");
        }
        return null;
    }


    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws JSONException
     */
    public function start(string $name, string $env): void
    {
        @f7b791fb1d06384599337402f8f9ae68($name, $env);
    }

    /**
     * @param string $name
     * @param string $env
     * @throws JSONException
     */
    public function stop(string $name, string $env): void
    {
        @e0363b4507cc5b1891d3775c32f04bde($name, $env);
    }

    /**
     * @param string $name
     * @param int $state
     * @param string $env
     * @return void
     */
    public function setState(string $name, int $state, string $env): void
    {
        @e3b4615fba4874ab133dfe6acb700890($name, $state, $env);
    }


    /**
     * @param string $name
     * @param string $env
     * @return array
     */
    public function getState(string $name, string $env): array
    {
        return @cfb6ad5dda2af960948a27546a092608($name, $env);
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
        return $this->unsafeHook($env, $point, $type, ...$arg);
    }

    /**
     * @param string $env
     * @param int $point
     * @param int $type
     * @param ...$arg
     * @return array|string|bool|Response
     * @throws \ReflectionException
     */
    public function unsafeHook(string $env, int $point, int $type = PGN::HOOK_TYPE_PAGE, &...$arg): array|string|bool|Response
    {
        $this->setRuntime();
        $var = $this->runtime[$env][$point] ?? [];
        $global = $this->runtime["GLOBAL"][$point] ?? [];

        foreach ($global as $g) {
            $var[] = $g;
        }

        $results = "";
        /**
         * @var HookInfo $runtime
         */
        foreach ($var as $runtime) {
            $class = Di::instance()->make($runtime->namespace, $runtime->plugin);
            Di::instance()->inject($class);
            $action = $runtime->method;
            $result = $class->$action(...$arg);
            if ($type == PGN::HOOK_TYPE_PAGE && is_string($result)) {
                $results .= $result;
            } elseif ($type == PGN::HOOK_TYPE_HTTP && $result instanceof Response && $result->getOptions("forced_end")) {
                return $result;
            } elseif ($type == PGN::HOOK_TYPE_PAGE && is_array($result)) {
                if ($results === "") {
                    $results = [];
                }
                $results[] = $result;
            } elseif ($type == PGN::HOOK_TYPE_PAGE && is_bool($result)) {
                return $result;
            }
        }
        return $results;
    }


    /**
     * @param array $users
     * @param int $point
     * @param int $type
     * @param ...$arg
     * @return array|string|bool|Response
     * @throws \ReflectionException
     */
    public function unsafeMultiHook(array $users, int $point, int $type = PGN::HOOK_TYPE_PAGE, &...$arg): array|string|bool|Response
    {
        $results = "";
        $users = array_unique(array_map(function ($item) {
            return !$item ? '*' : $item;
        }, $users));
        foreach ($users as $user) {
            $result = $this->unsafeHook($user == "*" ? Usr::MAIN : Usr::inst()->userToEnv($user), $point, $type, ...$arg);
            if ($type == PGN::HOOK_TYPE_PAGE && is_string($result)) {
                $results .= $result;
            } elseif ($type == PGN::HOOK_TYPE_HTTP && $result instanceof Response && $result->getOptions("forced_end")) {
                return $result;
            } elseif ($type == PGN::HOOK_TYPE_PAGE && is_array($result)) {
                if ($results === "") {
                    $results = [];
                }
                $results[] = $result;
            } elseif ($type == PGN::HOOK_TYPE_PAGE && is_bool($result)) {
                return $result;
            }
        }
        return $results;
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
        return $this->unsafeMultiHook($users, $point, $type, ...$arg);
    }


    /**
     * @param string $name
     * @param string $env
     * @param int $point
     * @param ...$arg
     * @return void
     * @throws \ReflectionException
     */
    public function instantHook(string $name, string $env, int $point, &...$arg): void
    {
        Hook::inst()->scan($name, $env, function (int $p, HookInfo $runtime) use (&$arg, $point) {
            if ($point === $p) {
                $class = Di::instance()->make($runtime->namespace, $runtime->plugin);
                $action = $runtime->method;
                $class->$action(...$arg);
            }
        });
    }


    /**
     * @return void
     */
    public function setRuntime(): void
    {
        $runtime = BASE_PATH . "/runtime/plugin/hook";
        if (!file_exists($runtime)) {
            return;
        }
        $this->runtime = File::read($runtime, function (string $contents) {
            return $this->decrypt($contents);
        }) ?: [];
    }

    /**
     * @param string $action
     * @param string $name
     * @param string $env
     * @return void
     * @throws \ReflectionException
     */
    public function database(string $action, string $name, string $env): void
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return;
        }
        if (!isset($plugin->handle) || !isset($plugin->handle[Database::class])) {
            return;
        }
        $handle = $plugin->handle[Database::class];
        if (!class_exists($handle)) {
            return;
        }

        $usr = Usr::inst()->envToUsr($env);
        $obj = new $handle($plugin, $usr);
        Di::instance()->inject($obj);
        call_user_func([$obj, $action]);
    }

    /**
     * @param string $name
     * @param string $env
     * @param string $interface
     * @param mixed ...$args
     * @return mixed
     * @throws \ReflectionException
     */
    public function getHandle(string $name, string $env, string $interface, mixed &...$args): mixed
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return null;
        }
        if ($plugin->state['run'] != 1) {
            return null;
        }
        if (!isset($plugin->handle) || !isset($plugin->handle[$interface])) {
            return null;
        }

        $handle = $plugin->handle[$interface];
        if (!class_exists($handle)) {
            return null;
        }

        $obj = new $handle($plugin, ...$args);
        Di::inst()->inject($obj);
        return $obj;
    }

    /**
     * @param int $type
     * @param string $env
     * @return Entity[]
     */
    public
    function getStartups(int $type = PGN::TYPE_GENERAL, string $env = "/app/Plugin"): array
    {
        return @bd580eb07f5781f020e46ed277c0fe52($type, $env);
    }


    /**
     * @param string $name
     * @param string $env
     * @return string|null
     * @throws \ReflectionException
     */
    public
    function getPayViewPath(string $name, string $env): ?string
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return null;
        }
        if ($plugin->state['run'] != 1) {
            return null;
        }
        $path = $plugin->path . "/View/Pay/";
        if (!file_exists($path)) {
            return null;
        }
        return $path;
    }


    /**
     * @param string $hash
     * @param string $name
     * @param string $env
     * @return UpdateLog|null
     * @throws \ReflectionException
     */
    public function getLogs(string $hash, string $name, string $env): ?UpdateLog
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return null;
        }

        $file = $plugin->path . "/Runtime";
        if (!file_exists($file)) {
            file_put_contents($file, "");
        }

        $timeout = 0;

        //3秒超时
        while ($timeout <= 60) {
            clearstatcache();
            $md5 = md5_file($file);
            if ($md5 != $hash) {
                $hash = $md5;
                break;
            } else {
                usleep(50000);
            }

            $timeout++;
        }


        $maxSize = 1024 * 128;
        $fileSize = filesize($file);
        $bytesToRead = min($fileSize, $maxSize);

        if ($bytesToRead <= 0) {
            return new UpdateLog($hash, "");
        }

        $fileObject = new \SplFileObject($file, 'r');
        $fileObject->fseek($fileSize - $bytesToRead);
        $data = $fileObject->fread($bytesToRead);
        $lines = explode(PHP_EOL, trim($data));
        $lines = array_slice($lines, -2000);

        return new UpdateLog($hash, implode(PHP_EOL, $lines));
    }

    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws \ReflectionException
     */
    public
    function clearLog(string $name, string $env): void
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return;
        }

        $file = $plugin->path . "/Runtime";
        if (!is_file($file)) {
            return;
        }

        file_put_contents($file, "");
    }

    /**
     * @param string $name
     * @param string $env
     * @param array $config
     * @return void
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public
    function setConfig(string $name, string $env, array $config): void
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return;
        }
        $pass = $this->getHwId();
        File::write($plugin->path . "/Config/Config", Aes::encrypt(serialize($config), $pass, $pass, false));
    }

    /**
     * @param string $name
     * @param string $env
     * @return array
     */
    public
    function getConfig(string $name, string $env): array
    {
        $runtime = BASE_PATH . "/{$env}/{$name}/Config/Config";
        if (!is_file($runtime)) {
            return [];
        }
        return File::read($runtime, function (string $contents) use ($env) {
            $pass = $this->getHwId();
            return unserialize(Aes::decrypt($contents, $pass, $pass, false)) ?: [];
        });
    }

    /**
     * @param string $name
     * @param string $env
     * @param array $config
     * @return void
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public
    function setSystemConfig(string $name, string $env, array $config): void
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return;
        }
        File::writeForLock($plugin->path . "/Config/System", function (string $contents) use ($config) {
            $pass = $this->getHwId();
            $configs = unserialize(Aes::decrypt($contents, $pass, $pass, false)) ?: [];
            foreach ($config as $key => $val) {
                $configs[$key] = $val;
            }
            return Aes::encrypt(serialize($configs), $pass, $pass, false);
        });
    }


    /**
     * @param string $name
     * @param string $env
     * @return array
     */
    public
    function getSystemConfig(string $name, string $env): array
    {
        $runtime = BASE_PATH . "/{$env}/{$name}/Config/System";

        if (!is_file($runtime)) {
            return [];
        }

        return File::read($runtime, function (string $contents) use ($env) {
            $pass = $this->getHwId();
            return unserialize(Aes::decrypt($contents, $pass, $pass, false)) ?: [];
        });
    }


    /**
     * @throws RuntimeException
     */
    public
    function encrypt(array $data): string
    {
        $pass = $this->getHwId();
        return @Aes::encrypt(serialize($data), $pass, $pass, false);
    }


    /**
     * @param string $data
     * @return mixed
     */
    public
    function decrypt(string $data): array
    {
        $pass = $this->getHwId();
        return @unserialize(Aes::decrypt($data, $pass, $pass, false)) ?: [];
    }
}