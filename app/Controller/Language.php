<?php
declare (strict_types=1);

namespace App\Controller;

use App\Controller\User\Base;
use Kernel\Context\App;
use Kernel\Context\Interface\Response;
use Kernel\Util\Context;

class Language extends Base
{
    /**
     * @return Response
     */
    public function pack(): Response
    {
        /**
         * @var \Kernel\Language\Entity\Language $var
         */
        $var = Context::get(\Kernel\Language\Entity\Language::class);
        $language = strtolower($var->preferred);
        $languagePack = array_merge(\Kernel\Language\Language::instance()->getLanguagePack($language), \Kernel\Plugin\Language::instance()->packs($language, App::env()));
        return $this->response->withHeader("Content-Type", "application/json; charset=utf-8")->raw(json_encode($languagePack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }


    /**
     * @param string $t
     * @return Response
     */
    public function record(string $t): Response
    {
        //\Kernel\Language\Language::inst()->recordSource(trim($t));
        return $this->response->json();
    }
}