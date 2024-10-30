!function () {
    let table = null;

    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/admin/user/group/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "权限组图标",
                            name: "icon",
                            type: "image",
                            placeholder: "请选择图标",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 64,
                            required: true
                        },
                        {
                            title: "权限组名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入等级名称",
                            required: true
                        },
                        {
                            title: "开通方式",
                            name: "open_mode",
                            type: "radio",
                            dict: [
                                {id: 0, name: "免费开通"},
                                {id: 1, name: "付费开通"}
                            ],
                            change: (form, value) => {
                                if (value == 1) {
                                    form.show("price");
                                    form.show("dividend_amount");
                                    form.getMap("price") == 0 && form.setInput("price", "");
                                } else {
                                    form.hide("price");
                                    form.hide("dividend_amount");
                                    form.setInput("price", "0");
                                }
                            },
                            complete: (form, value) => {
                                if (assign?.price > 0) {
                                    form.show("price");
                                    form.show("dividend_amount");
                                    form.setRadio("open_mode", 1, true);
                                } else {
                                    form.setRadio("open_mode", 0, true);
                                }
                            }
                        },
                        {
                            title: "付费价格",
                            name: "price",
                            type: "input",
                            placeholder: "请填写开通价格",
                            hide: true
                        },
                        {
                            title: "商家分红",
                            name: "dividend_amount",
                            type: "input",
                            placeholder: "分红金额",
                            tips: "当商家的会员在店铺中开通商家权限后，商家将有资格获得相应的分红金额。",
                            hide: true
                        },
                        {
                            title: "商家权限",
                            name: "is_merchant",
                            type: "switch",
                            placeholder: "开启|关闭"
                        },
                        {
                            title: "供货权限",
                            name: "is_supplier",
                            type: "switch",
                            placeholder: "开启|关闭"
                        },
                        {
                            title: "税率",
                            name: "tax_ratio",
                            type: "input",
                            placeholder: "税率，例如：0.01",
                            tips: "税率是什么？<br>商家如果售卖自己供货的商品，则按照此税率计算税金，影响范围：商品出售、会员等级出售"
                        },
                        {
                            title: "显示排序",
                            name: "sort",
                            type: "input",
                            placeholder: "越小，显示在越前面"
                        },
                        {
                            title: "可购买",
                            name: "is_upgradable",
                            type: "switch",
                            placeholder: "启用|关闭"
                        }
                    ]
                }
            ],
            assign: assign,
            autoPosition: true,
            height: "auto",
            done: () => {
                table.refresh();
            }
        });
    }

    table = new Table("/admin/user/group/get", "#user-group-table");
    table.setUpdate("/admin/user/group/save");
    table.disablePagination();
    table.setColumns([
        {
            field: 'name', title: '权限组名称', formatter: (name, row) => {
                return format.group(row);
            }
        },
        {field: 'user_count', title: '商家数量'},
        {
            field: 'price', title: '开通价格', formatter: price => {
                if (price == 0) {
                    return "免费开通";
                }

                return price;
            }
        },
        {field: 'is_merchant', title: '商家权限', type: "switch", reload: true, text: "开启|关闭"},
        {field: 'is_supplier', title: '供货权限', type: "switch", reload: true, text: "开启|关闭"},
        {field: 'is_upgradable', title: '可购买', type: "switch", reload: true, text: "开启|关闭"},
        {
            field: 'tax_ratio', title: '税率', formatter: amount => {
                return (amount * 100) + "%";
            }
        },
        {
            field: 'dividend_amount',
            title: '商家分红'
        },
        {field: 'sort', title: '显示排序', type: 'input', reload: true, width: 100},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-a-xiugai2") + "<space></space> 修改权限组", row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        message.ask("是否删除该权限组？", () => {
                            util.post("/admin/user/group/del", {id: row.id}, () => {
                                table.refresh();
                                layer.msg("删除成功");
                            })
                        });
                    }
                }
            ], width: 110
        },
    ]);
    table.render();

    $('.add-user-group').click(() => {
        modal(util.icon("icon-tianjia") + "<space></space>添加权限组");
    });
}();