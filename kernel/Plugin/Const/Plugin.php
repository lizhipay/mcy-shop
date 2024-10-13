<?php
declare (strict_types=1);

namespace Kernel\Plugin\Const;

interface Plugin
{
    const NAME = 'name';
    const AUTHOR = 'author';
    const DESCRIPTION = 'desc';
    const VERSION = 'version';
    const ARCH = 'arch';
    const HOOK_SCOPE = 'scope';

    const TYPE = "type";

    const ARCH_CLI = 1;
    const ARCH_FPM = 2;

    const HOOK_TYPE_PAGE = 1;
    const HOOK_TYPE_HTTP = 2;

    //仅主站使用，全局插件
    const HOOK_SCOPE_GLOBAL = 1;

    //分站/主站都可以使用的插件
    const HOOK_SCOPE_USR = 2;

    const TYPE_GENERAL = 1; //通用插件
    const TYPE_PAY = 2; //支付插件
    const TYPE_SHIP = 4; //货源插件

    const TYPE_THEME = 8; //模版主题
    const TYPE_ANY = 16; //全部插件


    const STATE_START = 1;
    const STATE_SYNC = 2;
    const STATE_STOP = 0;

    const HANDLE_PAY = "pay";
    const HANDLE_SHIP = "ship";
}