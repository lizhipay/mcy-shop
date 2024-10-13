!function () {
    let table = null;
    const modal = (title, assign = {}) => {
        let hide = !assign.hasOwnProperty('id');
        component.popup({
            submit: '/user/pay/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "图标",
                            name: "icon",
                            type: "image",
                            placeholder: "请选择图标",
                            uploadUrl: '/user/upload',
                            photoAlbumUrl: '/user/upload/get',
                            height: 64,
                            required: true,
                            hide: hide
                        },
                        {
                            title: "支付名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入支付接口名称",
                            tips: "该名称主要是显示在前台，客户选择支付方式所显示的",
                            required: true,
                            hide: hide
                        },
                        {
                            title: "支付插件",
                            name: "plugin",
                            type: "select",
                            submit: false,
                            required: true,
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
                                popup.hide("scope");
                                popup.hide("api_fee_status");
                                if (val == "") {
                                    return;
                                }
                                util.post({
                                    url: "/user/pay/code?plugin=" + val,
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

                                _Dict.advanced(`pluginConfig?plugin=${val}&handle=pay`, d => {
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
                            required: true,
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
                            required: true,
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
                                popup.show("scope");
                                popup.show("api_fee_status");
                            }
                        },
                        {
                            title: "支持业务",
                            name: "scope",
                            tag: true,
                            type: "checkbox",
                            placeholder: "排序",
                            dict: [
                                {id: "product", name: "购物"},
                                {id: "level", name: "等级"}
                            ],
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
                }
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

    const borrowModal = (title, assign = {}) => {
        component.popup({
            submit: '/user/pay/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "图标",
                            name: "icon",
                            required: true,
                            type: "image",
                            placeholder: "请选择图标",
                            uploadUrl: '/user/upload',
                            photoAlbumUrl: '/user/upload/get',
                            height: 64
                        },
                        {
                            title: "支付名称",
                            name: "name",
                            required: true,
                            type: "input",
                            placeholder: "请输入支付接口名称",
                            tips: "该名称主要是显示在前台，客户选择支付方式所显示的",
                        },
                        {
                            title: "上级接口",
                            required: true,
                            name: "pid",
                            type: "select",
                            dict: "masterPay",
                            placeholder: "请选择上级接口",
                        },
                        {
                            title: "排序",
                            name: "sort",
                            type: "input",
                            placeholder: "排序"
                        },
                        {
                            title: "终端",
                            name: "equipment",
                            type: "radio",
                            placeholder: "排序",
                            dict: "pay_equipment"
                        },
                        {
                            title: "状态", name: "status", type: "switch"
                        }
                    ]
                }
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


    table = new Table("/user/pay/get", "#pay-table");
    table.setDeleteSelector(".del-pay", "/user/pay/del");
    table.setPagination(10, [10, 20, 30, 50]);
    table.setUpdate("/user/pay/save");
    table.setColumns([
        {checkbox: true},
        {field: 'name', title: '接口名称', formatter: format.category},
        {
            field: 'plugin', title: '插件', formatter: function (plugin, item) {
                if (item.pid > 0) {
                    return format.color('inherit', '#2ed232');
                }
                if (!plugin) {
                    return '-';
                }
                return plugin.info.name + '(' + plugin.info.version + ')';
            }
        },
        {
            field: 'code', title: '支付通道', formatter: function (code, item) {
                if (item.pid > 0) {
                    return format.color('inherit', '#2ed232');
                }
                if (!item.plugin) {
                    return '-';
                }
                return item.plugin.payCode[code];
            }
        },
        {
            field: 'config', title: '配置文件', formatter: function (config, item) {
                if (item.pid > 0) {
                    return format.color('inherit', '#2ed232');
                }

                if (!item.config) {
                    return '-';
                }
                return item.config.name;
            }
        },
        {field: 'equipment', title: '支持设备', dict: "pay_equipment"},
        {
            field: 'api_fee',
            title: 'API手续费',
            width: 100,
            type: 'input',
            show: item => !item.pid,
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
                        if (row.pid > 0) {
                            borrowModal(util.icon('icon-peizhixinxi') + " 修改支付接口", row);
                        } else {
                            modal(util.icon('icon-peizhixinxi') + " 修改支付接口", row);
                        }
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/user/pay/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.setFloatMessage([
        {
            field: 'plugin', title: '接口类型', formatter: function (plugin, item) {
                if (item.pid > 0) {
                    return format.color('官方接口', '#2ed232');
                }
                return format.color('自定义', '#fd2020');
            }
        },
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
    table.setSearch([
        {title: "接口名称(模糊搜索)", name: "search-name", type: "input"},
        {title: "支付插件", name: "equal-plugin", type: "select", dict: "pay"},
    ]);
    table.setState("equipment", "pay_equipment");
    table.render();


    $('.add-pay').click(() => {
        modal(util.icon("icon-tianjia") + " 新增个人支付接口");
    });

    $('.borrow-pay').click(() => {
        borrowModal(util.icon("icon-web__kuaisuduijie") + " 新增平台支付接口");
    });
}();