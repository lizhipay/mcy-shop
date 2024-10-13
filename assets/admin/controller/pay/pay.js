!function () {
    let table = null, payGroupTable = null, payUserTable = null;
    const modal = (title, assign = {}) => {
        let hide = !assign.hasOwnProperty('id');
        const tempId = util.generateRandStr(16);

        component.popup({
            submit: '/admin/pay/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            name: "temp_id",
                            type: "input",
                            hide: true,
                            default: tempId
                        },
                        {
                            title: "图标",
                            name: "icon",
                            type: "image",
                            placeholder: "请选择图标",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 64,
                            hide: hide,
                            required: true
                        },
                        {
                            title: "支付名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入支付接口名称",
                            tips: "该名称主要是显示在前台，客户选择支付方式所显示的",
                            hide: hide,
                            required: true
                        },
                        {
                            title: "支付插件",
                            name: "plugin",
                            type: "select",
                            submit: false,
                            dict: "pay",
                            placeholder: "请选择插件",
                            change: (popup, val) => {
                                popup.clearOption("pay_config_id");
                                popup.clearOption("code");

                                popup.hide("icon");
                                popup.hide("code");
                                popup.hide("name");
                                popup.hide("pay_config_id");
                                popup.hide("sort");
                                popup.hide("trade_status");
                                popup.hide("recharge_status");
                                popup.hide("equipment");
                                popup.hide("status");
                                popup.hide("scope");
                                popup.hide("substation_status");
                                popup.hide("substation_fee");
                                popup.hide("api_fee_status");

                                if (val == "") {
                                    return;
                                }


                                util.post({
                                    url: "/admin/pay/code?plugin=" + val,
                                    done: res => {
                                        for (const resKey in res.data) {
                                            popup.addOption("code", resKey, res.data[resKey]);
                                        }
                                        popup.show("code");

                                        if (!hide) {
                                            popup.setSelected("code", assign.code);
                                        }
                                    }
                                });

                                _Dict.advanced(`pluginConfig?plugin=${val}&handle=pay&userId=${assign?.user_id}`, d => {
                                    d.forEach(e => {
                                        popup.addOption("pay_config_id", e.id, e.name);
                                    });

                                    if (!hide) {
                                        popup.setSelected("pay_config_id", assign.pay_config_id);
                                    }
                                });
                            },
                            complete: (popup, val) => {
                                if (!hide) {
                                    popup.setSelected("plugin", assign.config.plugin)
                                }
                            }
                        },
                        {
                            title: "支付通道",
                            name: "code",
                            type: "select",
                            placeholder: "请选择支付通道",
                            hide: hide,
                            change: (popup, val) => {
                                if (val == "") {
                                    popup.hide('pay_config_id');
                                    return;
                                }
                                popup.show('pay_config_id');
                            }
                        },
                        {
                            title: "支付配置",
                            name: "pay_config_id",
                            type: "select",
                            placeholder: "请选择支付配置文件",
                            hide: hide,
                            change: (popup, val) => {
                                if (val == "") {
                                    return;
                                }

                                popup.show("icon");
                                popup.show("code");
                                popup.show("name");
                                popup.show("sort");
                                popup.show("trade_status");
                                popup.show("recharge_status");
                                popup.show("equipment");
                                popup.show("status");
                                popup.show("scope");
                                popup.show("substation_status");
                                popup.show("api_fee_status");

                                if (assign?.substation_status == 1) {
                                    popup.show("substation_fee");
                                }
                            }
                        },
                        {
                            title: "支持业务",
                            name: "scope",
                            tag: true,
                            type: "checkbox",
                            placeholder: "排序",
                            dict: "pay_scope",
                            hide: hide
                        },
                        {
                            title: "API->付费调用",
                            name: "api_fee_status",
                            type: "switch",
                            hide: hide,
                            tips: "使用此支付接口，需要额外增加手续费，注意：此功能影响到任意地方调用",
                            change: (popup, val) => {
                                popup.setInput("api_fee", 0);
                                if (val == 1) {
                                    popup.show("api_fee");
                                } else {
                                    popup.hide("api_fee");
                                }
                            },
                            default: (assign?.api_fee == 0 || !assign?.api_fee) ? 0 : 1
                        },
                        {
                            title: "API->手续费比例",
                            name: "api_fee",
                            type: "number",
                            placeholder: "手续费百分比",
                            default: "0.000",
                            tips: "手续费百分比，使用小数表示，如：0.001则是千分之一",
                            hide: assign?.api_fee == 0 || !assign?.api_fee
                        },
                        {
                            title: "支持分站",
                            name: "substation_status",
                            type: "switch",
                            hide: hide,
                            tips: "如果开启此选项，所有分站都可以使用该接口，你只想对某个商户等级开启或某个商家开启，可以关闭此功能后在用户组或会员中进行单独开启",
                            change: (popup, val) => {
                                if (val == 1) {
                                    popup.show("substation_fee");
                                } else {
                                    popup.hide("substation_fee");
                                }
                            }
                        },
                        {
                            title: "分站手续费",
                            name: "substation_fee",
                            type: "number",
                            placeholder: "手续费百分比",
                            default: "0.000",
                            tips: "分站如果使用该接口，可以设置一定的手续费比例，如：0.001则是千分之一",
                            hide: assign?.substation_status == 0 || !assign.hasOwnProperty("substation_status")
                        },
                        {
                            title: "排序",
                            name: "sort",
                            type: "input",
                            placeholder: "排序",
                            hide: hide
                        },
                        {
                            title: "终端",
                            name: "equipment",
                            type: "radio",
                            placeholder: "排序",
                            dict: "pay_equipment",
                            hide: hide
                        },
                        {
                            title: "状态", name: "status", type: "switch", hide: hide
                        }
                    ]
                },
                {
                    name: util.icon("icon-tuandui") + " 用户组",
                    form: [
                        {
                            name: "group",
                            type: "custom",
                            complete: (popup, dom) => {
                                const updateUrl = "/admin/pay/group/save?payId=" + (assign.id ?? tempId) + "&type=" + (assign.id ? "real" : "temp");

                                dom.html(`<div class="block block-rounded"> <div class="block-content mt-0 pt-0"><table id="pay-group-table"></table> </div> </div>`);
                                payGroupTable = new Table("/admin/pay/group/get?id=" + (assign.id ?? tempId) + "&type=" + (assign.id ? "real" : "temp"), dom.find('#pay-group-table'));
                                payGroupTable.setColumns([
                                    {
                                        field: 'name', title: '用户组', formatter: (name, item) => {
                                            return format.group(item);
                                        }
                                    },
                                    {
                                        field: 'fee',
                                        title: '手续费',
                                        type: 'input',
                                        width: 95,
                                        formatter: format.amountRemoveTrailingZeros
                                    },
                                    {
                                        field: 'status',
                                        title: '状态',
                                        type: 'switch',
                                        text: "启用|关闭",
                                        width: 100
                                    }
                                ]);

                                payGroupTable.setUpdate(updateUrl);
                                payGroupTable.disablePagination();
                                payGroupTable.render();
                            }
                        }
                    ]
                },
                {
                    name: util.icon("icon-yonghu") + " 分站",
                    form: [
                        {
                            name: "user",
                            type: "custom",
                            complete: (popup, dom) => {
                                const updateUrl = "/admin/pay/user/save?payId=" + (assign.id ?? tempId) + "&type=" + (assign.id ? "real" : "temp");
                                dom.html(`<div class="block block-rounded"><div class="block-content mt-0 pt-0"><table id="pay-user-table"></table></div></div>`);
                                payUserTable = new Table("/admin/pay/user/get?id=" + (assign.id ?? tempId) + "&type=" + (assign.id ? "real" : "temp"), dom.find('#pay-user-table'));
                                payUserTable.setUpdate(updateUrl);
                                payUserTable.setColumns([
                                    {
                                        field: 'username', title: '商家', formatter: function (val, item) {
                                            return format.user(item);
                                        }
                                    },
                                    {
                                        field: 'fee',
                                        title: '手续费',
                                        type: 'input',
                                        width: 95,
                                        formatter: format.amountRemoveTrailingZeros
                                    },
                                    {
                                        field: 'status',
                                        title: '状态',
                                        type: 'switch',
                                        text: "启用|关闭",
                                        width: 100
                                    }
                                ]);
                                payUserTable.setSearch([
                                    {title: "ID", name: "equal-id", type: "input", width: 90},
                                    {title: "用户名", name: "equal-username", type: "input", width: 125},
                                    {title: "备注", name: "search-note", type: "input", width: 125}
                                ]);
                                payUserTable.render();
                            }
                        }
                    ]
                },
            ],
            assign: assign,
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            height: "auto",
            width: "680px",
            done: () => {
                table.refresh();
            }
        });
    }

    table = new Table("/admin/pay/get", "#pay-table");
    table.setDeleteSelector(".del-pay", "/admin/pay/del");
    table.setUpdate("/admin/pay/save");
    table.setPagination(15, [15, 20, 30, 50]);
    table.setColumns([
        {checkbox: true},
        {
            field: 'user', title: '商户', formatter: format.user, class: "nowrap"
        },
        {field: 'name', title: '接口名称', formatter: format.category, class: "nowrap"},
        {
            field: 'parent', title: '钱的去向', formatter: function (parent, item) {
                if (item.user && item.plugin && !item.parent) {
                    return format.primary(i18n("商家"));
                }
                return format.success(i18n("平台"));
            }, class: "nowrap"
        },
        {
            field: 'plugin', title: '插件', class: "nowrap", formatter: function (plugin, item) {
                if (item.parent) {
                    return `继承: ${format.category(item.parent)}`;
                }
                if (!plugin) {
                    return '-';
                }
                return plugin.info.name + '(' + plugin.info.version + ')';
            }
        },
        {
            field: 'code', title: '支付通道', class: "nowrap", formatter: function (code, item) {
                if (!item.plugin) {
                    return '-';
                }
                return item.plugin.payCode[code];
            }
        },
        {
            field: 'config', title: '配置文件', class: "nowrap", formatter: function (config, item) {
                if (!item.config) {
                    return '-';
                }
                return item.config.name;
            }
        },
        {field: 'equipment', class: "nowrap", title: '支持设备', dict: "pay_equipment"},

        {
            field: 'substation_status',
            title: '分站',
            class: "nowrap",
            type: "switch",
            text: "启用|关闭",
            reload: true,
            show: item => !item.user
        },
        {
            field: 'substation_fee',
            title: '分站手续费',
            width: 95,
            type: 'input',
            reload: true,
            show: item => !item.user
        },
        {
            field: 'api_fee',
            title: 'API手续费',
            width: 95,
            type: 'input',
            show: item => !item.user,
            reload: true
        },
        {field: 'status', title: '总开关', class: "nowrap", type: "switch", text: "启用|关闭", reload: true},
        {field: 'sort', title: '排序', width: 65, type: 'input', reload: true, sort: true},
        {
            field: 'operation', title: '操作', width: 110, type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        modal(util.icon('icon-peizhixinxi') + " 修改支付接口", row);
                    },
                    show: item => !item.user
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/pay/del", [row.id], () => {
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
                {id: 2, name: "仅商家"}
            ], change: (search, val) => {
                if (val == 2) {
                    search.show("user_id");
                } else {
                    search.hide("user_id");
                }

                if (val == 1) {
                    search.show("equal-plugin");
                } else {
                    search.hide("equal-plugin");
                }
                search.selectReload("equal-plugin", "pay");
            }
        },
        {
            title: "搜索商家",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=2",
            hide: true,
            change: (search, id, selected) => {
                if (selected) {
                    search.show("equal-plugin");
                    search.selectReload("equal-plugin", "pay?userId=" + id);
                } else {
                    search.hide("equal-plugin");
                    search.selectReload("equal-plugin", "pay");
                }
            }
        },
        {title: "支付插件", name: "equal-plugin", type: "select", dict: "pay", hide: true},
        {title: "接口名称(模糊搜索)", name: "search-name", type: "input"},
    ]);
    table.setState("equipment", "pay_equipment");
    table.setFloatMessage([
        {field: 'today_amount', title: '今日收款'},
        {field: 'yesterday_amount', title: '昨日收款'},
        {field: 'weekday_amount', title: '本周收款'},
        {field: 'month_amount', title: '本月收款'},
        {field: 'last_month_amount', title: '上月收款'},
        {field: 'order_amount', title: '总收款'},
        {
            field: 'scope', title: '接口业务', class: "nowrap", formatter: (scope, item) => {
                if (scope.length == 0) {
                    return '-';
                }

                let html = "";
                scope.forEach(g => {
                    html += format.badge(_Dict.result("pay_scope", g), "acg-badge-h-green nowrap");
                });
                return html;
            }
        },
        {field: 'create_time', title: '创建时间'}
    ]);
    table.render();

    $('.add-pay').click(() => {
        modal(util.icon("icon-tianjia") + " 新增支付接口");
    });
}();