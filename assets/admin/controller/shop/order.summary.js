!function () {
    const table = new Table("/admin/shop/order/summary/get", "#user-order-summary-table");
    table.setColumns([
        {
            field: 'user', title: '会员', formatter: format.user
        },
        {field: 'date', title: '日期'},
        {
            field: 'product_amount', title: '商品订单', formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce");
            }
        },
        {
            field: 'recharge_amount', title: '充值订单', formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce");
            }
        },
        {
            field: 'group_amount', title: '升级用户组订单', formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce");
            }
        },
        {
            field: 'level_amount', title: '升级等级订单', formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce");
            }
        },
        {
            field: 'plugin_amount', title: '第三方订单', formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce");
            }
        }
    ]);
    table.setPagination(15, [15, 50, 100, 500]);
    table.setSearch([
        {
            title: "商家，默认主站",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=2"
        },
        {
            title: "时间",
            name: "between-time",
            type: "date"
        },
    ]);
    table.setState("date_type", "order_summary_date_type");
    table.render();
}();