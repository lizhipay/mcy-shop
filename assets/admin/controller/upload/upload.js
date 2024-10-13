!function () {
    let table = null;

    table = new Table("/admin/upload/get", "#upload-table");
    table.setTree(3);
    table.setDeleteSelector(".del-file", "/admin/upload/del");
    table.setPagination(15, [15, 20, 50, 100, 500, 1000, 2000, 5000]);
    table.setColumns([
        {checkbox: true},
        {
            field: 'user', title: '会员', formatter: format.user
        },
        {
            field: 'path', title: '文件路径', formatter: (value) => {
                return `<a href="${value}" target="_blank">${value}</a>`;
            }
        },
        {field: 'hash', title: 'hash'},
        {field: 'type', title: '文件类型', dict: "upload_file_type"},
        {field: 'create_time', title: '上传时间'},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/upload/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.setSearch([
        {
            title: "显示范围：整站", name: "display_scope", type: "select", dict: [
                {id: 1, name: "仅主站"},
                {id: 2, name: "仅会员"}
            ], change: (search, val) => {
                if (val == 2) {
                    search.show("user_id");
                } else {
                    search.hide("user_id");
                }
            }
        },
        {
            title: "搜索会员",
            name: "user_id",
            type: "remoteSelect",
            dict: "user",
            hide: true
        },
    ]);
    table.render();
}();