!function () {
    let table = null;
    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/admin/user/bank/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "银行图标",
                            name: "icon",
                            type: "image",
                            placeholder: "请选择图标",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 64,
                            required: true
                        },
                        {
                            title: "银行名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入银行名称",
                            required: true
                        },
                        {
                            title: "银行代码",
                            name: "code",
                            type: "input",
                            placeholder: "请输入银行代码",
                            required: true
                        },
                        {title: "状态", name: "status", type: "switch"},
                    ]
                }
            ],
            autoPosition: true,
            height: "auto",
            width: "540px",
            assign: assign,
            done: () => {
                table.refresh();
            }
        });
    }

    table = new Table("/admin/user/bank/get", "#bank-table");
    table.setDeleteSelector(".del-bank", "/admin/user/bank/del");
    table.setUpdate("/admin/user/bank/save");
    table.disablePagination();
    table.setColumns([
        {checkbox: true},
        {field: 'name', title: '银行名称', formatter: format.category},
        {field: 'code', title: '银行代码'},
        {field: 'status', title: '状态', type: "switch", text: "启用|关闭", class: "nowrap"},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-a-xiugai2") + "<space></space> 修改银行", row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/bank/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.render();

    $('.add-bank').click(() => {
        modal(util.icon("icon-tianjia") + "<space></space>新增银行");
    });
}();