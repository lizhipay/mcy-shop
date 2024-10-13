<?php
declare (strict_types=1);

namespace App\Const;

interface Order
{

    //购买商品
    const ORDER_TYPE_PRODUCT = 0;

    //充值
    const ORDER_TYPE_RECHARGE = 1;

    //升级用户组
    const ORDER_TYPE_UPGRADE_GROUP = 2;

    //升级会员等级
    const ORDER_TYPE_UPGRADE_LEVEL = 3;

    //插件购买
    const ORDER_TYPE_PLUGIN = 49;


    //主站
    const AUTO_RECEIPT_ROLE_MAIN = 0;
    //商家
    const AUTO_RECEIPT_ROLE_MERCHANT = 1;
    //供货商
    const AUTO_RECEIPT_ROLE_SUPPLIER = 2;
    //客户
    const AUTO_RECEIPT_ROLE_CUSTOMER = 3;
}