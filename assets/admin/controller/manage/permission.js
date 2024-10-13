!function () {
    let table = null;
    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/admin/permission/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "上级权限",
                            name: "pid",
                            type: "treeSelect",
                            dict: "permission?type=1",
                            placeholder: "上级权限，空则为顶级"
                        },
                        {
                            title: "菜单图标",
                            name: "icon",
                            type: "image",
                            placeholder: "替换系统图标",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 100,
                            complete: (form, value) => {
                                if (!value || value == "" || /^icon-/.test(value)) {
                                    form.setImage("icon", "");
                                }
                            }
                        },
                        {
                            title: "权限名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入权限名称",
                            required: true
                        }
                    ]
                }
            ],
            width : "520px",
            assign: assign,
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            height: "auto",
            done: () => {
                table.refresh();
            },
        });
    }

    table = new Table("/admin/permission/get", "#permission-table");
    table.setTree(1, "name", "id", "pid", false);
    table.setUpdate("/admin/permission/save");
    table.setColumns([
        {field: 'rank', title: '排序', type: "input", reload: true, width: 65},
        {
            field: 'name', title: '权限名称', formatter: (value, item) => {
                let icon = "";
                if (/icon-/.test(item.icon)) {
                    icon = util.icon(item.icon, "item-icon");
                } else if (item.icon) {
                    icon = `<img src="${item.icon}" class="item-icon">`;
                }
                return `<span class="common-item">${icon} <span class="item-name">${value}</span></span>`;
            }
        },
        {field: 'type', title: '权限类型', dict: "permission_type"},
        {field: 'route', title: '路由'},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    tips: '修改权限',
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-a-xiugai2") + " 修改权限", row);
                    }
                }
            ]
        },
    ]);

    table.disablePagination();
    table.render();
}();