!function () {
    const table = new Table("/user/shop/order/summary/get", "#user-order-summary-table");
    table.setColumns([
        {field: 'date', title: '日期'},
        {
            field: 'amount', title: '订单金额', formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        }
    ]);
    table.setPagination(15, [15, 50, 100, 500]);
    table.setSearch([
        {
            title: "时间",
            name: "between-time",
            type: "date"
        },
    ]);
    table.setState("date_type", "order_summary_date_type");
    table.render();
}();