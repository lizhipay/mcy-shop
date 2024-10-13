!function () {
    let table = null;

    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/admin/role/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "角色名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入角色名称",
                            required: true
                        },
                        {title: "权限", name: "permission", type: "treeCheckbox", dict: 'permission'},
                        {title: "状态", name: "status", type: "switch"},
                    ]
                }
            ],
            assign: assign,
            autoPosition: true,
            height: "auto",
            width: "720px",
            done: () => {
                table.refresh();
            }
        });
    }

    table = new Table("/admin/role/get", "#role-table");
    table.setDeleteSelector(".del-role", "/admin/role/del");
    table.setUpdate("/admin/role/save");
    table.setColumns([
        {field: 'name', title: '角色名称'},
        {field: 'create_time', title: '创建时间'},
        {field: 'status', title: '状态', type: "switch", text: "启用|关闭",  class: "nowrap"},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        let map = Object.assign({}, row);
                        map.permission = component.idObjToList(row.permission);
                        modal(util.icon("icon-a-xiugai2") + " 修改角色", map);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/role/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.render();


    $('.add-role').click(() => {
        modal(util.icon("icon-tianjia") + " 添加角色");
    });
}();