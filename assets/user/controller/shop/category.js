!function () {
    let table = null;
    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/user/shop/category/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "父级分类",
                            name: "pid",
                            type: "treeSelect",
                            dict: "shopCategory",
                            placeholder: "父级分类，可不选",
                        },
                        {
                            title: "图标",
                            name: "icon",
                            type: "image",
                            placeholder: "请选择图标",
                            uploadUrl: '/user/upload',
                            photoAlbumUrl: '/user/upload/get',
                            height: 64,
                            required: true
                        },
                        {
                            title: "分类名称",
                            name: "name",
                            type: "textarea",
                            height: 34,
                            placeholder: "请输入分类名称（支持HTML美化）",
                            required: true
                        },
                        {
                            title: "排序",
                            name: "sort",
                            type: "input",
                            placeholder: "排序",
                            sort: true
                        },
                        {title: "状态", name: "status", type: "switch"},
                    ]
                }
            ],
            assign: assign,
            done: () => {
                table.refresh();
            }
        });
    }

    table = new Table("/user/shop/category/get", "#shop-category-table");
    table.setDeleteSelector(".del-shop-category", "/user/shop/category/del");
    table.setUpdate("/user/shop/category/save");
    table.disablePagination();
    table.setTree(2);
    table.setColumns([
        {checkbox: true},
        {field: 'icon', title: '图标', type: "image", style: "border-radius:25%;"},
        {
            field: 'name', title: '分类名称'
        },
        {field: 'item_all_count', title: '总商品'},
        {field: 'item_shelf_count', title: '在售'},
        {
            field: 'item_no_shelf_count', title: '下架', formatter: (no, item) => {
                return item.item_all_count - item.item_shelf_count;
            }
        },
        {field: 'sort', title: '排序', type: 'input', reload: true, sort: true, width: 100},
        {field: 'status', title: '状态', type: "switch", text: "启用|关闭", class: "nowrap"},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-a-xiugai2") + "<space></space>修改分类", row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/user/shop/category/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.setFloatMessage([
        {field: 'create_time', title: '创建时间'},
    ]);
    table.setSearch([{title: "分类名称", name: "search-name", type: "input"}]);
    table.render();


    $('.add-shop-category').click(() => {
        modal(util.icon("icon-tianjia") + "<space></space>添加分类");
    });
}();