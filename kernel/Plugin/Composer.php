<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use App\Service\Common\Config;
use Kernel\Annotation\Inject;
use Kernel\Component\Singleton;
use Kernel\Context\App;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ServiceException;
use Kernel\Util\Aes;
use Kernel\Util\File;
use Kernel\Util\Shell;

class Composer
{
    use Singleton;

    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/composer";
    #[Inject]
    private Config $config;


    private string $workingDir;
    private string $composer;

    public function __construct()
    {
        $this->workingDir = realpath(BASE_PATH);
        $this->composer = (App::$cli ? "COMPOSER_ALLOW_SUPERUSER=1 " : "") . realpath(BASE_PATH . "bin") . " " . realpath(BASE_PATH . "composer.phar");
    }

    /**
     * @param array $option
     * @return void
     * @throws \ReflectionException
     */
    public function exec(array $option): void
    {
        if (App::$cli) {
            switch ($option['command']) {
                case 'require':
                    Shell::inst()->exec("sudo {$this->composer} require {$option['packages'][0]} --no-interaction --prefer-source --working-dir={$this->workingDir}");
                    break;
                case 'remove':
                    Shell::inst()->exec("sudo {$this->composer} remove {$option['packages'][0]} --no-interaction --working-dir={$this->workingDir}");
                    break;
                case 'config':
                    Shell::inst()->exec("sudo {$this->composer} config repo.packagist composer {$option['value']} --no-interaction --working-dir={$this->workingDir}");
                    break;
            }
            return;
        }

        if (!class_exists('\Symfony\Component\Console\Input\ArrayInput')) {
            require("phar://{$this->workingDir}/composer.phar/src/bootstrap.php");
        }

        //@\putenv('COMPOSER_ALLOW_SUPERUSER=1');
        //@\putenv('COMPOSER_HOME=' . $this->workingDir . '/vendor/bin/composer');

        $_ENV['COMPOSER_ALLOW_SUPERUSER'] = 1;
        $_ENV['COMPOSER_HOME'] = $this->workingDir . '/vendor/bin/composer';

        $output = new \Symfony\Component\Console\Output\ConsoleOutput(\Symfony\Component\Console\Output\ConsoleOutput::VERBOSITY_QUIET);
        $input = new \Symfony\Component\Console\Input\ArrayInput(array_merge([
            '--no-interaction' => true,
            '--working-dir' => $this->workingDir,
            '--no-progress' => true,
            '--quiet' => true
        ], $option));
        $application = new \Composer\Console\Application();
        $application->setAutoExit(false);
        $application->run($input, $output);
    }


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function updatePackagist(): void
    {
        if (!App::$install) {
            return;
        }

        $config = $this->config->getMainConfig("composer");
        if (!isset($config['server'])) {
            return;
        }

        $packagist = match ($config['server']) {
            "aliyun" => "https://mirrors.aliyun.com/composer/",
            "tencent" => "https://mirrors.tencent.com/composer/",
            "huaweicloud" => "https://mirrors.huaweicloud.com/repository/php/",
            "custom" => $config['custom_url'],
            default => "https://packagist.org"
        };

        //Shell::inst()->exec("sudo {$this->composer} config repo.packagist composer {$packagist} --no-interaction --working-dir={$this->workingDir}");

        $this->exec([
            'command' => 'config',
            'setting-key' => 'repo.packagist',
            'setting-type' => 'composer',
            'value' => $packagist
        ]);
    }

    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function install(string $name, string $env): void
    {
        $plugin = Plugin::inst()->getPlugin($name, $env);

        if (!$plugin) {
            throw new ServiceException("插件不存在");
        }

        $composer = BASE_PATH . $env . "/{$name}/Config/Composer.php";
        $systemComposer = BASE_PATH . "/composer.json";

        if (!is_file($composer)) {
            return;
        }

        //更新源
        $this->updatePackagist();

        $systemComposerList = json_decode(file_get_contents($systemComposer), true);

        $require = require($composer);
        if (is_array($require)) {
            foreach ($require as $package => $version) {
                if (!isset($systemComposerList['require'][$package])) {
                    //安装依赖
                    //$result = Shell::inst()->exec("sudo {$this->composer} require {$package}:{$version} --no-interaction --prefer-source --working-dir={$this->workingDir}");
                    //$plugin->log($result, true);

                    $this->exec([
                        'command' => 'require',
                        'packages' => ["{$package}:{$version}"]
                    ]);

                    //安装后重新检查
                    $systemComposerList = json_decode(file_get_contents($systemComposer), true);
                    if (!isset($systemComposerList['require'][$package])) {
                        throw new ServiceException("Composer依赖安装失败，请到程序根目录执行命令手动安装：mcy composer.require {$package}:{$version}");
                    }
                }

                //写入依赖
                File::writeForLock(self::CACHE_FILE, function (string $contents) use ($version, $env, $name, $package) {
                    $pass = Plugin::inst()->getHwId();
                    $composers = unserialize(Aes::decrypt($contents, $pass, $pass, false)) ?: [];
                    $composers[] = [
                        "package" => $package,
                        "version" => $version,
                        "name" => $name,
                        "env" => $env,
                    ];
                    return Aes::encrypt(serialize($composers), $pass, $pass, false);
                });
            }
        }
    }

    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws ServiceException
     * @throws \ReflectionException
     * @throws RuntimeException
     */
    public function uninstall(string $name, string $env): void
    {
        $composer = BASE_PATH . $env . "/{$name}/Config/Composer.php";
        $systemComposer = BASE_PATH . "/composer.json";

        if (!is_file($composer)) {
            return;
        }

        $systemComposerList = json_decode(file_get_contents($systemComposer), true);

        $require = require($composer);
        if (is_array($require)) {
            foreach ($require as $package => $version) {
                $isDelete = true;
                File::writeForLock(self::CACHE_FILE, function (string $contents) use ($name, $env, $package, &$isDelete) {
                    $pass = Plugin::inst()->getHwId();
                    $composers = unserialize(Aes::decrypt($contents, $pass, $pass, false)) ?: [];
                    foreach ($composers as $index => $co) {
                        if ($co['package'] == $package) {
                            if ($co['name'] == $name && $co['env'] == $env) {
                                unset($composers[$index]);
                            } else {
                                $isDelete = false;
                            }
                        }
                    }
                    $composers = array_values($composers);
                    return Aes::encrypt(serialize($composers), $pass, $pass, false);
                });

                if (isset($systemComposerList['require'][$package]) && $isDelete) {
                    //移除依赖
                    // Shell::inst()->exec("sudo {$this->composer} remove {$package} --no-interaction --working-dir={$this->workingDir}");
                    $this->exec([
                        'command' => 'remove',
                        'packages' => [$package]
                    ]);

                    //移除后重新检查
                    $systemComposerList = json_decode(file_get_contents($systemComposer), true);
                    if (isset($systemComposerList['require'][$package])) {
                        throw new ServiceException("Composer依赖移除失败，请到程序根目录执行命令手动移除再进行插件卸载：mcy composer.remove {$package}");
                    }
                }
            }
        }
    }
}