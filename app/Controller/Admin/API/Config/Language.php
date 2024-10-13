<?php
declare (strict_types=1);

namespace App\Controller\Admin\API\Config;

use App\Controller\Admin\Base;
use App\Interceptor\Admin;
use App\Interceptor\PostDecrypt;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;

#[Interceptor(class: [PostDecrypt::class, Admin::class], type: Interceptor::API)]
class Language extends Base
{
    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function get(): Response
    {
        $sources = \Kernel\Language\Language::inst()->getSources($this->request->post("keywords"), (int)$this->request->post("limit"), (int)$this->request->post("page"));
        return $this->json(data: $sources, ext: ["languages" => array_values(array_filter(\Kernel\Util\Config::get("language")['languages'], fn($language) => $language['code'] != \Kernel\Language\Const\Language::ZH_CN))]);
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function save(): Response
    {
        $text = trim($this->request->post("zh_cn") ?? "");
        if (!$text) {
            throw new JSONException("原文不能为空");
        }

        if (!preg_match("/[\x{4e00}-\x{9fa5}]+/u", $text)) {
            throw new JSONException("原文中没有需要翻译的中文");
        }
        $languages = array_values(array_filter(\Kernel\Util\Config::get("language")['languages'], fn($language) => $language['code'] != \Kernel\Language\Const\Language::ZH_CN));
        foreach ($languages as $language) {
            $translate = trim($this->request->post(str_replace("-", "_", strtolower($language['code']))) ?? "");
            if (!$translate) {
                throw new JSONException($language['language'] . "({$language['localCountryName']}) 译文不能为空");
            }
            \Kernel\Language\Language::inst()->createLanguagePack($language['code'], $text, $translate);
        }

        return $this->json();
    }

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function del(): Response
    {
        $arr = $this->request->post("source") ?: [];
        if (!is_array($arr) || count($arr) == 0) {
            throw new JSONException("要删除的国际化原文不能为空");
        }
        foreach ($arr as $text) {
            \Kernel\Language\Language::inst()->clearSource(trim($text));
        }
        return $this->json();
    }
}