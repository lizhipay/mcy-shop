<?php
declare (strict_types=1);

namespace Kernel\Language\Const;

/**
 *语言代码遵循：ISO 639-1、ISO 3166-1 alpha-2标准
 * 第一部分是语言代码，遵循ISO 639-1标准。ISO 639是国际标准化组织（ISO）定义的语言代码列表，旨在为世界上所有主要的语言提供短代码。
 * 第二部分是国家或地区代码，遵循ISO 3166-1 alpha-2标准，ISO 3166是关于国家和地区代码的国际标准，用于代表国家和某些地区的简短代码。
 */
interface Language
{
    //简体中文
    public const ZH_CN = "zh-CN";

    //英语（美国）
    public const EN_US = "en-US";

    //法语
    public const FR_FR = "fr-FR";

    //德语
    public const DE_DE = "de-DE";

    //西班牙语
    public const ES_ES = "es-ES";

    //繁体（台湾）
    public const ZH_TW = "zh-TW";

    //日语
    public const JA_JP = "ja-JP";

    //俄语
    public const RU_RU = "ru-RU";

    //韩语
    public const KO_KR = "ko-KR";

    // 意大利语
    public const IT_IT = "it-IT";

    //印地语
    public const HI_IN = "hi-IN";

    //葡萄牙语
    public const PT_BR = "pt-BR";
}