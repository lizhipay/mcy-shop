!function () {

    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/admin/repertory/category/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "父级分类",
                            name: "pid",
                            type: "treeSelect",
                            dict: "repertoryCategory",
                            placeholder: "父级分类，可不选"
                        },
                        {
                            title: "图标",
                            name: "icon",
                            type: "image",
                            placeholder: "请选择图标",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 64,
                            required: true
                        },
                        {
                            title: "分类名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入分类名称",
                            required: true
                        },
                        {
                            title: "排序",
                            name: "sort",
                            type: "input",
                            placeholder: "排序"
                        },
                        {title: "状态", name: "status", type: "switch"},
                    ]
                }
            ],
            assign: assign,
            autoPosition: true,
            height: "auto",
            width: "520px",
            done: () => {
                table.refresh();
            }
        });
    }

    const table = new Table("/admin/repertory/category/get", "#repertory-category-table");
    table.setTree(2);
    table.disablePagination();
    table.setColumns([
        {checkbox: true},
        {field: 'icon', title: '', type: "image", style: "border-radius:25%;", width: 28},
        {field: 'name', title: '分类名称'},
        {field: 'create_time', title: '创建时间'},
        {field: 'sort', title: '排序', type: 'input', reload: true},
        {field: 'status', title: '状态', type: "switch", text: "启用|关闭", reload: true, class: "nowrap"},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        modal(`${util.icon("icon-a-xiugai2")} 修改分类`, row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/repertory/category/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.setUpdate("/admin/repertory/category/save");
    table.setDeleteSelector(".del-repertory-category", "/admin/repertory/category/del");
   /* table.setState("status", "repertory_category_status");*/
    table.render();

    $('.add-repertory-category').click(() => {
        modal(`${util.icon("icon-tianjia")} 添加分类`);
    });
}();