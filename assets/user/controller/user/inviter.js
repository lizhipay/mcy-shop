!function () {
    const table = new Table("/user/bill/get", "#user-inviter-table");
    table.setColumns([
        {
            field: 'amount', title: '返佣金额', formatter: (amount, item) => {
                return format.money(amount, "#70ef5c");
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
        {field: 'trade_no', title: '商品订单号'},
        {field: 'create_time', title: '返佣时间'}
    ]);
    table.setPagination(13, [13, 30, 50]);
    table.setWhere("equal-type", 10);
    table.render();


    $('.copy-invite-url').click(() => {
        util.copyTextToClipboard($('.invite-url').val(), () => {
            layer.msg("推广链接已复制");
        }, () => {
            layer.msg("您的浏览器不支持自动复制，请手动复制");
        });
    });
}();