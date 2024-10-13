<?php
declare (strict_types=1);

namespace App\Command;

use Kernel\Console\Command;
use Kernel\Context\App;
use Kernel\Exception\RuntimeException;

class Language extends Command
{

    /**
     * @param string $text
     * @param string $translation
     * @param string $code
     * @return void
     * @throws RuntimeException
     */
    public function createPack(string $text, string $translation, string $code): void
    {
        if (count($this->param) != 3) {
            $this->error("参数不完整");
            return;
        }

        \Kernel\Language\Language::instance()->createLanguagePack($code, $text, $translation);
        $this->success(sprintf("语言包创建成功，目标语言：%s，原文：%s，译文：%s", $code, $text, $translation));
    }

    /**
     * @throws RuntimeException
     */
    public function delPack(string $text, string $code): void
    {
        if (count($this->param) != 2) {
            $this->error("参数不完整");
            return;
        }
        if (\Kernel\Language\Language::instance()->deleteLanguagePack($code, $text)) {
            $this->success(sprintf("语言包删除成功，删除语言：%s，原文：%s", $code, $text));
        } else {
            $this->error(sprintf("未找到该译文，删除语言：%s，原文：%s", $code, $text));
        }
    }

    /**
     * @throws RuntimeException
     */
    public function delAllPack(): void
    {
        foreach ($this->param as $text) {
            foreach (App::$language['languages'] as $lan) {
                if ($lan['code'] != \Kernel\Language\Const\Language::ZH_CN) {
                    if (\Kernel\Language\Language::instance()->deleteLanguagePack($lan['code'], $text)) {
                        $this->success(sprintf("语言包删除成功，删除语言：%s(%s)，原文：%s", $lan['localCountryName'], $lan['code'], $text));
                    } else {
                        $this->error(sprintf("未找到该译文，删除语言：%s(%s)，原文：%s", $lan['localCountryName'], $lan['code'], $text));
                    }
                }
            }
        }
    }

    public function getCode(): void
    {
        foreach (App::$language['languages'] as $lan) {
            if ($lan['code'] != \Kernel\Language\Const\Language::ZH_CN) {
                $this->success(sprintf("语言：%s，代码：%s", $lan['localCountryName'], $lan['code']));
            }
        }
    }
}