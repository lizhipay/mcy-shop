<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Component\Singleton;
use Kernel\Exception\RuntimeException;
use Kernel\Util\File;
use Symfony\Component\Finder\Finder;

class Language
{
    use Singleton;

    /**
     * @var array
     */
    private array $languages = [];


    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/language/";

    /**
     * @param string $name
     * @param string $env
     * @return array
     */
    public function list(string $name, string $env = "/app/Plugin"): array
    {
        $path = BASE_PATH . $env . "/{$name}/Config/Language";
        if (!is_dir($path)) {
            return [];
        }

        $finder = Finder::create()->in($path)->depth("== 0")->name('*.json')->files();
        $list = [];
        foreach ($finder as $file) {
            $list[] = $file->getBasename('.json');
        }
        return $list;
    }

    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     */
    public function add(string $name, string $env = "/app/Plugin"): void
    {
        $dir = self::CACHE_FILE . md5($env);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $list = $this->list($name, $env);
        foreach ($list as $language) {
            $pluginPath = BASE_PATH . $env . "/{$name}/Config/Language/{$language}.json";
            if (!is_file($pluginPath)) {
                continue;
            }

            $json = json_decode((string)file_get_contents($pluginPath), true) ?: [];
            if (empty($json)) {
                continue;
            }

            $path = $dir . "/{$language}.json";
            File::writeForLock($path, function (string $contents) use ($json, $name) {
                $lgs = json_decode($contents, true) ?: [];
                if (!isset($lgs[$name])) {
                    $lgs[$name] = $json;
                }
                return json_encode($lgs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            });
        }
    }


    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     */
    public function del(string $name, string $env = "/app/Plugin"): void
    {
        $dir = self::CACHE_FILE . md5($env);
        if (!is_dir($dir)) {
            return;
        }
        $finder = Finder::create()->in($dir)->depth("== 0")->name('*.json')->files();
        foreach ($finder as $file) {
            File::writeForLock($file->getRealPath(), function (string $contents) use ($name) {
                $lgs = json_decode($contents, true) ?: [];
                unset($lgs[$name]);
                return json_encode($lgs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            });
        }
    }

    /**
     * @param string $language
     * @param string $env
     * @return string
     */
    public function hash(string $language, string $env = "/app/Plugin"): string
    {
        $language = strtolower($language);
        $path = self::CACHE_FILE . md5($env) . "/{$language}.json";
        if (!file_exists($path)) {
            return md5("none");
        }
        return md5_file($path);
    }


    /**
     * @param string $language
     * @param string $env
     * @return array
     */
    public function packs(string $language, string $env = "/app/Plugin"): array
    {
        $language = strtolower($language);
        $key = md5($language . $env);
        if (isset($this->languages[$key])) {
            return $this->languages[$key];
        }

        $path = self::CACHE_FILE . md5($env) . "/{$language}.json";
        if (!file_exists($path)) {
            return [];
        }
        $data = File::read($path, function (string $contents) {
            return json_decode($contents, true) ?: [];
        }) ?: [];

        $languages = [];
        foreach ($data as $datum) {
            $languages = array_merge($languages, $datum);
        }
        $this->languages[$key] = $languages;
        return $languages;
    }
}