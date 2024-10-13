<?php
declare (strict_types=1);

namespace Kernel\Language;

use Kernel\Component\Singleton;
use Kernel\Context\App;
use Kernel\Context\Interface\Request;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Const\Plugin as PGC;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Util\Config;
use Kernel\Util\Context;
use Kernel\Util\File;

class Language
{

    use Singleton;

    /**
     * 语言包路径
     * @var string
     */
    public string $languagePackPath = BASE_PATH . "/config/language";


    /**
     * @var array|null
     */
    private ?array $languages = null;


    /**
     * 内存缓存
     * @var array
     */
    private array $cache = [];

    /**
     * 输出国际化内容
     * @param string $text
     * @return string
     */
    public function output(string $text): string
    {
        if ($text === "0") {
            return $text;
        }

        $text = trim($text);

        if (!$text) {
            return "";
        }

        try {
            //检测是否包含中文
            if (!preg_match("/[\x{4e00}-\x{9fa5}]+/u", $text)) {
                return $text;
            }

            // \Kernel\Language\Language::inst()->recordSource($text);

            /**
             * @var Entity\Language $language
             */
            $language = Context::get(Entity\Language::class);
            if (!$language) {
                return $text;
            }

            if ($language->preferred == strtolower(Const\Language::ZH_CN)) {
                return $text;
            }

            $packName = strtolower($language->preferred);
            $md5 = md5($text);
            $_env = App::env();

            $cacheKey = "{$_env}_{$packName}_{$md5}";

            if (array_key_exists($cacheKey, $this->cache)) {
                return $this->cache[$cacheKey];
            }


            if ($result = Plugin::instance()->hook($_env, Point::LANGUAGE_PROCESS_BEFORE, PGC::HOOK_TYPE_PAGE, $language, $md5, $text)) {
                return $result;
            }

            //这里将插件语言包替换系统语言，使得插件可翻译整个系统的语言
            $languagePack = array_merge($this->getLanguagePack($packName), \Kernel\Plugin\Language::instance()->packs($packName, App::env()));

            if (array_key_exists($md5, $languagePack)) {
                $translation = $languagePack[$md5];
                if ($result = Plugin::instance()->hook($_env, Point::LANGUAGE_PROCESS_MATCH_SUCCESS, PGC::HOOK_TYPE_PAGE, $language, $md5, $text, $translation)) {
                    return $result;
                }
                $this->cache[$cacheKey] = $translation; //缓存
                return $translation;
            }

            //如果没有语言包，返回原文
            if ($result = Plugin::instance()->hook($_env, Point::LANGUAGE_PROCESS_MATCH_FAILED, PGC::HOOK_TYPE_PAGE, $language, $md5, $text)) {
                return $result;
            }
            return $text;
        } catch (\Throwable $e) {
            return $text;
        }
    }

    /**
     * 获得首选语言
     * @param string|null $acceptLanguage
     * @return string
     */
    public function getAcceptPreferredLanguage(?string $acceptLanguage): string
    {
        $preferredLanguage = App::$language['default'];
        if (!$acceptLanguage) {
            return $preferredLanguage;
        }
        $languages = explode(',', $acceptLanguage);
        foreach ($languages as $lang) {
            $parts = explode(';', $lang);
            $languageCode = trim($parts[0]);
            if (preg_match('/^[a-z]{2}-[A-Z]{2}$/', $languageCode)) {
                $preferredLanguage = $languageCode;
                break;
            }
        }
        return trim($preferredLanguage);
    }

    /**
     * @param Request $request
     * @return Entity\Language
     */
    public function getPreferredLanguage(Request $request): Entity\Language
    {
        $preferredLanguage = $this->getAcceptPreferredLanguage($request->header("AcceptLanguage"));
        $preferredLanguage = $request->cookie("language") ?? $preferredLanguage;
        return new Entity\Language(strtolower($preferredLanguage));
    }

    /**
     * @param string $language
     * @param string $text
     * @param string $translateText
     * @return void
     * @throws RuntimeException
     */
    public function createLanguagePack(string $language, string $text, string $translateText): void
    {
        $language = strtolower($language);
        $languagePack = $this->languagePackPath . "/{$language}.json";
        $this->recordSource($text);
        File::writeForLock($languagePack, function (string $contents) use ($translateText, $text) {
            $map = $contents ? (array)json_decode($contents, true) : [];
            $map[md5(trim($text))] = $translateText;
            return json_encode($map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });
    }


    /**
     * @throws RuntimeException
     */
    public function recordSource(string $text): void
    {
        if (!$text) {
            return;
        }

        $source = $this->languagePackPath . "/zh-cn.json";
        File::writeForLock($source, function (string $contents) use ($text) {
            $list = json_decode($contents, true) ?: [];
            if (!in_array($text, $list)) {
                $list[] = $text;
            }
            return json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });
    }

    /**
     * @param string $text
     * @return void
     * @throws RuntimeException
     */
    public function clearSource(string $text): void
    {
        $text = trim($text);
        if (!$text) {
            return;
        }
        $source = $this->languagePackPath . "/zh-cn.json";
        File::writeForLock($source, function (string $contents) use ($text) {
            $list = json_decode($contents, true) ?: [];
            if ($index = array_search($text, $list)) {
                unset($list[$index]);
                $list = array_values($list);
            }
            return json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        //删除所有译文
        $languages = array_values(array_filter(\Kernel\Util\Config::get("language")['languages'], fn($language) => $language['code'] != Const\Language::ZH_CN));
        foreach ($languages as $language) {
            $this->deleteLanguagePack($language['code'], $text);
        }
    }


    /**
     * @param string|null $keywords
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getSources(?string $keywords = null, int $limit = 10, int $page = 1): array
    {
        $list = File::read($this->languagePackPath . "/zh-cn.json", function (string $contents) {
            return json_decode($contents, true) ?: [];
        }) ?: [];

        if ($keywords) {
            $list = array_values(array_filter($list, fn($text) => str_contains($text, $keywords)));
        }

        if ($limit === 0) {
            return $list;
        }

        $offset = max(0, ($page - 1) * $limit);
        $data = array_slice($list, $offset, $limit);
        $total = count($list);

        $languages = array_values(array_filter(Config::get("language")['languages'], fn($language) => $language['code'] != Const\Language::ZH_CN));

        $translate = array_reduce($languages, function ($carry, $language) {
            $carry[$language['code']] = $this->getLanguagePack($language['code']);
            return $carry;
        }, []);

        $items = array_map(function ($dat) use ($languages, $translate) {
            $translatedLanguages = array_map(function ($language) use ($translate, $dat) {
                $language['translate'] = $translate[$language['code']][md5($dat)] ?? null;
                return $language;
            }, $languages);

            return [
                "id" => md5($dat),
                "source" => $dat,
                "language" => $translatedLanguages
            ];
        }, $data);

        return [
            'list' => $items,
            'total' => $total
        ];
    }

    /**
     * @param string $language
     * @param string $text
     * @return bool
     */
    public function existLanguagePack(string $language, string $text): bool
    {
        $languages = $this->getLanguagePack($language);
        $hash = md5(trim($text));
        if (array_key_exists($hash, $languages)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $language
     * @param string $text
     * @return bool
     * @throws RuntimeException
     */
    public function deleteLanguagePack(string $language, string $text): bool
    {
        $language = strtolower($language);
        $languagePack = $this->languagePackPath . "/{$language}.json";

        if (is_file($languagePack)) {
            File::writeForLock($languagePack, function (string $contents) use ($text) {
                $map = (array)json_decode($contents, true);
                $hash = md5(trim($text));
                if (isset($map[$hash])) {
                    unset($map[$hash]);
                }
                return json_encode($map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            });
            return true;
        }
        return false;
    }


    /**
     * @param string $language
     * @param string $basePath
     * @return array
     */
    public function getLanguagePack(string $language, string $basePath = BASE_PATH . "/config/language"): array
    {
        $language = strtolower($language);

        if ($language == Const\Language::ZH_CN) {
            return [];
        }

        $language = strtolower($language);
        $preferredPack = "{$basePath}/{$language}.json";

        if (!is_file($preferredPack)) {
            return [];
        }

        return File::read($preferredPack, function (string $contents) {
            return json_decode($contents, true) ?: [];
        }) ?: [];
    }


    /**
     * @param string $language
     * @param string $basePath
     * @return string
     */
    public function getHash(string $language, string $basePath = BASE_PATH . "/config/language"): string
    {
        $language = strtolower($language);
        $preferredPack = "{$basePath}/{$language}.json";
        if (!is_file($preferredPack)) {
            return md5("none");
        }
        return md5_file($preferredPack);
    }
}