!function () {
    let table = null;
    table = new Table("/admin/user/bank/card/get", "#bank-card-table");
    table.setDeleteSelector(".del-bank-card", "/admin/user/bank/card/del");
    table.setColumns([
        {checkbox: true},
        {field: 'user', title: '会员', formatter: format.invite},
        {field: 'bank', title: '银行名称', formatter: format.category},
        {field: 'card_no', title: '银行卡号'},
        {field: 'card_image', title: '银行卡照片(补充)', type: "image"},
        {field: 'status', title: '状态', dict: "user_bank_card_status"},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    title: '标记异常',
                    icon: 'icon-yichang',
                    class: 'btn-table-primary',
                    click: (event, value, row, index) => {
                        util.post("/admin/user/bank/card/abnormality", {id: row.id, status: 0}, res => {
                            table.refresh();
                        });
                    },
                    show: item => {
                        return item.status == 1;
                    }
                },
                {
                    title: '解除异常',
                    icon: 'icon-jiefeng',
                    class: 'btn-table-success',
                    click: (event, value, row, index) => {
                        util.post("/admin/user/bank/card/abnormality", {id: row.id, status: 1}, res => {
                            table.refresh();
                        });
                    },
                    show: item => {
                        return item.status == 0;
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    title: '移除此卡',
                    class: 'btn-table-danger',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/user/bank/card/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.setPagination(15, [15, 20, 50, 100]);
    table.setSearch([
        {
            title: "搜索会员",
            name: "equal-user_id",
            type: "remoteSelect",
            dict: "user"
        },
        {
            title: "银行卡号",
            name: "equal-card_no",
            type: "input"
        }
    ]);

    table.setFloatMessage([
        {field: 'today_withdraw_amount', title: '今日提现'},
        {field: 'yesterday_withdraw_amount', title: '昨日提现'},
        {field: 'weekday_withdraw_amount', title: '本周提现'},
        {field: 'month_withdraw_amount', title: '本月提现'},
        {field: 'last_month_withdraw_amount', title: '上月提现'},
        {field: 'total_withdraw_amount', title: '总提现'}
    ]);
    table.setState("status", "user_bank_card_status");
    table.render();
}();