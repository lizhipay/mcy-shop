!function () {
    const table = new Table("/admin/user/bill/get", "#user-bill-table");
    table.setColumns([
        {
            field: 'user', title: '会员', formatter: format.user
        },
        {field: 'type', title: '账单类型', dict: 'user_bill_type'},
        {
            field: 'amount', title: '金额', formatter: (amount, item) => {
                const strikethrough = item.status == 2 ? 'strikethrough' : '';

                if (item.action == 1) {
                    return format.success("+" + amount, strikethrough);
                } else {
                    return format.danger("-" + amount, strikethrough);
                }
            }
        },
        {
            field: 'before_balance', title: '变更前余额', formatter: (balance, item) => {
                if (item.status != 0) {
                    return "-";
                }
                return format.money(balance, "#b09d9d");
            }
        },
        {
            field: 'after_balance', title: '变更后余额', formatter:  (balance, item) => {
                if (item.status != 0) {
                    return "-";
                }
                return format.money(balance, "#7ac1ff");
            }
        },
        {field: 'status', title: '状态', dict: 'user_bill_status'},
        {
            field: 'unfreeze_time', title: '解冻时间', formatter: (time, item) => {
                if (item.status == 1) {
                    return time;
                }
                return '-';
            }
        },
        {field: 'is_withdraw', title: '可提现', dict: 'user_bill_is_withdraw'},
        {field: 'trade_no', title: '关联订单号'},
        {field: 'remark', title: '账单备注'},
        {field: 'update_time', title: '变更时间'},
        {field: 'create_time', title: '创建时间'}
    ]);
    table.setPagination(10, [10, 30, 50, 100, 500]);
    table.setSearch([
        {
            title: "搜索会员",
            name: "user_id",
            type: "remoteSelect",
            dict: "user"
        },
        {
            title: "关联订单号",
            name: "equal-trade_no",
            type: "input"
        },
        {
            title: "订单备注",
            name: "equal-remark",
            type: "input"
        },
        {
            title: "账单状态",
            name: "equal-status",
            type: "select",
            dict: "user_bill_status"
        },
        {
            title: "创建时间",
            name: "between-create_time",
            type: "date"
        }
    ]);
    table.setState("type", "user_bill_type");
    table.render();
}();