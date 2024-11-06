<?php
declare (strict_types=1);

namespace Kernel\Plugin\Const;

interface Point
{
    public const APP_START_BEFORE = 0x1;
    public const APP_START_AFTER = 0x7;
    public const APP_STOP_BEFORE = 0x2;
    public const APP_STOP_AFTER = 0x8;
    public const APP_UNINSTALL_BEFORE = 0x3;
    public const APP_UNINSTALL_AFTER = 0x9;
    public const APP_INSTALL = 0x4;
    public const APP_UPGRADE_BEFORE = 0x5;
    public const APP_UPGRADE_AFTER = 0x10;

    public const APP_SAVE_CFG_BEFORE = 0x6;
    public const APP_SAVE_CFG_AFTER = 0x30;

    public const APP_SAVE_HANDLE_CFG_BEFORE = 0x31;
    public const APP_SAVE_HANDLE_CFG_AFTER = 0x32;

    public const APP_SAVE_PAY_CFG_BEFORE = 0x31; //未来移除，已废弃
    public const APP_SAVE_PAY_CFG_AFTER = 0x32; //未来移除，已废弃


    //启动后才能触发的HOOK
    public const KERNEL_INIT_BEFORE = 0x11;
    public const CLI_INIT_BEFORE = 0x12;
    public const CLI_INIT_AFTER = 0x13;

    public const HTTP_REQUEST_START = 0x14;
    public const HTTP_REQUEST_ENTER = 0x21;
    public const HTTP_NOT_FOUND = 0x15;
    public const HTTP_REQUEST_CONTROLLER = 0x16;

    public const HTTP_UPLOAD_SAVE_READY = 0x17;
    public const HTTP_UPLOAD_SAVE_COMPLETE = 0x18;

    public const TEMPLATE_COMPILE_BEFORE = 0x19;
    public const TEMPLATE_COMPILE_AFTER = 0x20;


    public const ADMIN_API_AUTH_LOGIN_BEFORE = 0x101;
    public const ADMIN_API_AUTH_LOGIN_AFTER = 0x102;
    public const ADMIN_INTERCEPTOR_SESSION_ONLINE = 0x103;
    public const ADMIN_INTERCEPTOR_SESSION_OFFLINE = 0x104;
    public const ADMIN_INTERCEPTOR_NOT_PERMISSION = 0x105;


    //auth
    public const ADMIN_AUTH_HEADER = 0x2001;
    public const ADMIN_AUTH_FOOTER = 0x2002;
    public const ADMIN_AUTH_LOGIN_BODY = 0x2003;


    //repertory_item
    public const ADMIN_REPERTORY_ITEM_POPUP = 0x2004;

    //支付订单页面表单按钮
    public const ADMIN_PAY_ORDER_TABLE_BUTTON = 0x9030;
    public const ADMIN_PAY_ORDER_BODY = 0x9031;

    //商品订单页面
    public const ADMIN_ITEM_ORDER_TABLE_BUTTON = 0x9034;
    public const ADMIN_ITEM_ORDER_BODY = 0x9035;


    //order
    public const HACK_ROUTE_TABLE_COLUMNS = 0x2005;
    public const HACK_SUBMIT_FORM = 0x9038;
    public const HACK_SUBMIT_TAB = 0x9039;

    public const SERVICE_SMTP_SEND_BEFORE = 0x3000;
    public const SERVICE_SMTP_SEND_SUCCESS = 0x3001;
    public const SERVICE_SMTP_SEND_ERROR = 0x3002;

    public const USER_AUTH_HEADER = 0x4000;
    public const USER_AUTH_FOOTER = 0x4001;
    public const USER_AUTH_REGISTER_BODY = 0x4002;
    public const USER_AUTH_REGISTER_FORM = 0x4003;
    public const USER_AUTH_REGISTER_FORM_BUTTON = 0x4009;

    public const USER_AUTH_LOGIN_BODY = 0x4004;
    public const USER_AUTH_LOGIN_FORM = 0x4005;
    public const USER_AUTH_LOGIN_FORM_BUTTON = 0x4008;

    public const USER_AUTH_RESET_BODY = 0x4006;
    public const USER_AUTH_RESET_FORM = 0x4007;
    public const USER_AUTH_RESET_FORM_BUTTON = 0x4010;

    public const USER_SECURITY_GENERAL_FORM = 0x4011;

    public const SERVICE_AUTH_SEND_EMAIL_BEFORE = 0x5000;
    public const SERVICE_AUTH_SEND_EMAIL_SUCCESS = 0x5001;
    public const SERVICE_AUTH_SEND_EMAIL_ERROR = 0x5002;


    public const SERVICE_AUTH_REGISTER_BEFORE = 0x5003;
    public const SERVICE_AUTH_REGISTER_READY = 0x5004;
    public const SERVICE_AUTH_REGISTER_SUCCESS = 0x5005;

    public const SERVICE_AUTH_LOGIN_BEFORE = 0x5006;
    public const SERVICE_AUTH_LOGIN_SUCCESS = 0x5007;

    public const SERVICE_AUTH_RESET_BEFORE = 0x5008;
    public const SERVICE_AUTH_RESET_SUCCESS = 0x5009;

    public const DB_QUERY_EXECUTED = 0x6000;


    public const LANGUAGE_PROCESS_BEFORE = 0x7000;
    public const LANGUAGE_PROCESS_MATCH_SUCCESS = 0x7001;
    public const LANGUAGE_PROCESS_MATCH_FAILED = 0x7002;


    // 商品详情页
    public const INDEX_ITEM_TRADE_BUTTON_AFTER = 0x8000;
    public const INDEX_ITEM_TRADE_FORM = 0x9012;
    public const INDEX_ITEM_BODY = 0x8001;
    public const INDEX_ITEM_HEAD = 0x8002;


    public const CONTROLLER_ORDER_TRADE_BEFORE = 0x9000;
    public const CONTROLLER_ORDER_TRADE_AFTER = 0x9001;


    public const SERVICE_ITEM_GET_ENTITY = 0x9002;


    public const SERVICE_QUERY_GET_BEFORE = 0x9005;
    public const SERVICE_QUERY_GET_RESULT = 0x9006;
    public const SERVICE_QUERY_SAVE_BEFORE = 0x9007;
    public const SERVICE_QUERY_SAVE_AFTER = 0x9008;
    public const SERVICE_QUERY_DELETE_BEFORE = 0x9009;
    public const SERVICE_QUERY_DELETE_SUCCESS = 0x9010;
    public const SERVICE_QUERY_DELETE_ERROR = 0x9011;

    public const SERVICE_ORDER_TRADE_BEFORE = 0x9013;
    public const SERVICE_ORDER_TRADE_CREATE_ITEM_BEFORE = 0x9017;
    public const SERVICE_ORDER_TRADE_CREATE_ITEM_READY = 0x9014;
    public const SERVICE_ORDER_TRADE_CREATE_ITEM_FINISH = 0x9016;
    public const SERVICE_ORDER_TRADE_AFTER = 0x9015;
    public const SERVICE_ORDER_TRADE_CREATE_ORDER = 0x9021;

    public const SERVICE_ORDER_DELIVER_PRODUCT_PAY_OWNER_MERCHANT = 0x9025;
    public const SERVICE_ORDER_DELIVER_PRODUCT_SUCCESS = 0x9028;
    public const SERVICE_ORDER_DELIVER_PRODUCT_ERROR = 0x9029;

    public const SERVICE_PAY_ORDER_THIRD_TRADE_BEFORE = 0x9018;
    public const SERVICE_PAY_ORDER_BALANCE_PAY = 0x9019;
    public const SERVICE_PAY_ORDER_THIRD_TRADE_AFTER = 0x9020;
    public const SERVICE_PAY_ORDER_ASYNC = 0x9024;

    public const SERVICE_REPERTORY_ORDER_TRADE_BEFORE = 0x9026;
    public const SERVICE_REPERTORY_ORDER_TRADE_AFTER = 0x9027;

    public const INDEX_HEADER = 0x9003;
    public const INDEX_FOOTER = 0x9004;
    public const INDEX_CHECKOUT_ITEM = 0x9022;
    public const INDEX_CHECKOUT_TOTAL_AMOUNT = 0x9023;

    //支付订单页面表单按钮
    public const USER_PAY_ORDER_TABLE_BUTTON = 0x9032;
    public const USER_PAY_ORDER_BODY = 0x9033;

    //商品订单页面
    public const USER_ITEM_ORDER_TABLE_BUTTON = 0x9036;
    public const USER_ITEM_ORDER_BODY = 0x9037;


    //分站站点service
    public const SERVICE_SITE_ADD_BEFORE = 0x9040;
    public const SERVICE_SITE_ADD_AFTER = 0x9041;
    public const SERVICE_SITE_MODIFY_CERTIFICATE_BEFORE = 0x9042;
    public const SERVICE_SITE_MODIFY_CERTIFICATE_AFTER = 0x9043;
    public const SERVICE_SITE_DEL_BEFORE = 0x9044;
    public const SERVICE_SITE_DEL_AFTER = 0x9045;


    public const SERVICE_CART_ADD_BEFORE = 0x9046;
    public const SERVICE_CART_ADD_AFTER = 0x9047;


    public const MODEL_REPERTORY_ORDER_SAVE = 0x9048;


    //用户中心全局header
    public const USER_COMMON_HEADER = 0x9049;
    public const USER_COMMON_FOOTER = 0x9050;
}