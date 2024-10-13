<?php
declare (strict_types=1);

namespace App\Const;

interface Balance
{
    //进货
    public const TYPE_RESTOCK = 0;

    //结算
    public const TYPE_SUPPLY_SETTLEMENT = 1;

    //下级分红
    public const TYPE_SUB_DIVIDEND = 2;

    //订单分红
    public const TYPE_ORDER_DIVIDEND = 3;

    //购物消费
    public const TYPE_SHOPPING = 4;

    //订单退款
    public const TYPE_ORDER_REFUND = 5;

    //供货商支付退款
    public const TYPE_PAY_ORDER_REFUND = 6;

    //人工操作
    public const TYPE_MANUAL = 7;

    //转账
    public const TYPE_TRANSFER = 8;

    //充值
    public const TYPE_RECHARGE = 9;
    //推广分红
    public const TYPE_INVITE_DIVIDEND = 10;

    //提现
    public const TYPE_WITHDRAW = 11;


    //提现被驳回
    public const TYPE_WITHDRAW_REJECT = 12;

    //押金
    public const TYPE_DEPOSIT = 13;

    //商品出售
    public const TYPE_GOODS_SALE = 14;

    //借款
    public const TYPE_LOAN = 15;

    //还款
    public const TYPE_REPAYMENT = 16;

    //拨款
    public const TYPE_APPROPRIATION = 17;


    //即时到账
    public const STATUS_DIRECT = 0;

    //延迟到账
    public const STATUS_DELAYED = 1;

    //回滚
    public const STATUS_ROLLBACK = 2;


    //收入
    public const ACTION_ADD = 1;

    //支出
    public const ACTION_DEDUCT = 0;
}