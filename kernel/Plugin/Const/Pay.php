<?php
declare (strict_types=1);

namespace Kernel\Plugin\Const;

interface Pay
{
    /**
     *  渲染方法：直接跳转
     */
    const RENDER_JUMP = 0;

    /**
     * 渲染方法：FORM表单提交
     */
    const RENDER_FORM_POST_SUBMIT = 1;

    /**
     * 渲染方法：本地插件视图渲染(根据CODE自动找插件目录文件)
     */
    const RENDER_LOCAL_PLUGIN_VIEW = 2;


    /**
     * 系统渲染：支付宝，需要提供qrcode
     */
    const RENDER_COMMON_ALIPAY_VIEW = 3;

    /**
     * 系统渲染：微信，需要提供qrcode
     */
    const RENDER_COMMON_WECHAT_VIEW = 4;


    /**
     * 系统渲染：QQ钱包
     */
    const RENDER_COMMON_QQ_VIEW = 5;
}