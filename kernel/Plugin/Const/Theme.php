<?php
declare (strict_types=1);

namespace Kernel\Plugin\Const;

interface Theme
{
    public const INDEX = 0x1;
    public const ITEM = 0x2;
    public const CHECKOUT = 0x3;
    public const SEARCH = 0x4;
    public const CART = 0x5;
    public const REGISTER = 0x10;
    public const LOGIN = 0x11;
    public const DASHBOARD = 0x12;
    public const STOCK_MARKET = 0x13;  //废弃
    public const USER_SHOP_CATEGORY = 0x14;
    public const USER_SHOP_ITEM = 0x15;
    public const USER_SHOP_SUPPLY = 0x16;
    public const USER_SHOP_SUPPLY_ORDER = 0x46;
    public const USER_CONFIG = 0x17;
    public const USER_ITEM_MARKUP_TEMPLATE = 0x18;

    public const USER_SHOP_ORDER = 0x19;


    public const USER_PLUGIN = 0x20;
    public const USER_PLUGIN_WIKI = 0x21;

    public const USER_PAY = 0x22;
    public const USER_PAY_ORDER = 0x23;

    public const USER_REPERTORY_ITEM = 0x24;


    public const USER_TRADE_ORDER = 0x25;

    public const USER_REPORT_ORDER = 0x26;

    public const USER_SUPPLY_REPORT_ORDER = 0x27;


    public const USER_REPERTORY_ORDER = 0x28;


    public const USER_BILL = 0x29;

    public const USER = 0x30;
    public const USER_LEVEL = 0x31;
    public const USER_RECHARGE = 0x32;

    public const USER_PERSONAL = 0x33;

    public const USER_SECURITY = 0x34;

    public const USER_INVITER = 0x35;

    public const USER_BANK_CARD = 0x36;

    public const USER_TRANSFER = 0x37;

    public const USER_WITHDRAW = 0x38;

    public const USER_OPEN_MERCHANT = 0x39;

    public const USER_SELF_LEVEL = 0x40;


    public const USER_SHOP_SUMMARY = 0x41;


    public const USER_LOGIN_LOG = 0x42;


    public const USER_STORE = 0x43;

    public const TERMS = 0x44;


    public const USER_REPERTORY_ITEM_MARKUP_TEMPLATE = 0X45;
}