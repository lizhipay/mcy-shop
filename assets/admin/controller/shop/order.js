!function () {
    const table = new Table("/admin/shop/order/get", "#shop-order-table");
    table.setPagination(12, [12, 20, 50, 100]);
    table.setColumns([
        {
            field: 'trade_no', title: '订单号'
        },
        {
            field: 'user', title: '商家信息', formatter: format.user
        },
        {field: 'customer', title: '会员', formatter: format.customer},
        {field: 'type', title: '订单类型', dict: "shop_order_type"},
        {
            field: 'total_amount', title: '订单金额', formatter: (amount, item) => {
                return format.money(amount, "#19bf5d");
            }
        },
        {field: 'status', title: '支付状态', dict: "shop_order_status"},
        {field: 'create_time', title: '下单时间'},
        {field: 'pay_time', title: '支付时间'},
        {field: 'create_ip', title: 'IP地址'},
        {field: 'create_browser', title: '浏览器'},
        {field: 'create_device', title: '设备'},
    ]);
    table.setSearch([
        {
            title: "显示范围：整站",
            name: "display_scope",
            type: "select",
            dict: [
                {id: 1, name: "仅主站"},
                {id: 2, name: "仅商家"}
            ],
            change: (search, val) => {
                if (val == 2) {
                    search.show("user_id");
                } else {
                    search.hide("user_id");
                }
            }
        },
        {
            title: "搜索商家",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=2",
            hide: true
        },
        {
            title: "订单类型",
            name: "equal-type",
            type: "select",
            dict: "shop_order_type"
        },
        {
            title: "订单号",
            name: "equal-trade_no",
            type: "input"
        },
        {
            title: "会员",
            name: "equal-customer_id",
            type: "remoteSelect",
            dict: "user"
        },
        {title: "下单时间", name: "between-create_time", type: "date"},
        {
            title: "IP地址",
            name: "equal-create_ip",
            type: "input"
        },
    ]);
    table.setState("status", "shop_order_status");
    table.onResponse(data => {
        $('.data-count .order-count').html(data.data.order_count);
        $('.data-count .order-amount').html(getVar("CCY") + data.data.order_amount);
    });
    table.render();
}();