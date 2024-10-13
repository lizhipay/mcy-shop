!function () {
    const table = new Table("/admin/user/identity/get", "#user-identity-table");
    table.setColumns([
        {
            field: 'user', title: '会员', formatter: format.user
        },
        {field: 'type', title: '证件类型', dict: 'user_identity_type'},
        {field: 'name', title: '姓名'},
        {field: 'id_card', title: '证件号码'},
        {field: 'status', title: '状态', dict: 'user_identity_status'},
        {field: 'review_time', title: '审核时间'},
        {field: 'create_time', title: '提交时间'},
        {
            field: 'operation', title: '审核', type: 'button', buttons: [
                {
                    icon: 'icon-chenggong',
                    class: 'btn-outline-success',
                    click: (event, value, row, index) => {
                        util.post({
                            url: "/admin/user/identity/save",
                            data: {id: row.id, status: 1},
                            done: res => {
                                table.refresh();
                            }
                        });
                    },
                    show: item => {
                        return item.status == 0;
                    }
                },
                {
                    icon: 'icon-cuowu',
                    class: 'btn-outline-danger',
                    click: (event, value, row, index) => {
                        util.post({
                            url: "/admin/user/identity/save",
                            data: {id: row.id, status: 2},
                            done: res => {
                                table.refresh();
                            }
                        });
                    },
                    show: item => {
                        return item.status == 0;
                    }
                }
            ]
        },
    ]);
    table.setPagination(15, [15, 30, 50, 100, 500]);
    table.setSearch([
        {
            title: "搜索会员",
            name: "equal-user_id",
            type: "remoteSelect",
            dict: "user"
        }
    ]);
    table.setState("status", "user_identity_status");
    table.onResponse(res => {
        $('.data-count .identity-count').html(res.count);
        $('.data-count .identity-review').html(res.not_reviewed_count);
        $('.data-count .identity-success').html(res.not_success_count);
        $('.data-count .identity-error').html(res.count - res.not_success_count - res.not_reviewed_count);
    });
    table.render();
}();