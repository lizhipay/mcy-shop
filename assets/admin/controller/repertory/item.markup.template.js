!function () {
    let table = null;
    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/admin/repertory/item/markup/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "模板名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入加价模板名称",
                            required: true
                        },
                        {
                            title: false,
                            name: "price_module",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(`<div class="module-header">${i18n('同步价格模块')}</div>`);
                            }
                        },
                        {
                            title: "同步价格",
                            name: "sync_amount",
                            type: "radio",
                            dict: [
                                {id: 0, name: "🚫不同步"},
                                {id: 1, name: "💲同步并自定义价格"},
                                {id: 2, name: "♻️同步上游"}
                            ],
                            required: true,
                            tips: "不同步：完全由本地自定义价格\n同步并加价：根据上游的商品价格实时控制盈亏\n同步上游：上游是什么价格，本地商品就是什么价格".replaceAll("\n", "<br>"),
                            change: (from, val) => {
                                val = parseInt(val);
                                switch (val) {
                                    case 0:
                                        from.hide('exchange_rate');
                                        from.hide('keep_decimals');
                                        from.hide('drift_base_amount');
                                        from.hide('drift_model');
                                        from.hide('drift_value');
                                        break;
                                    case 1:
                                        from.show('exchange_rate');
                                        from.show('keep_decimals');
                                        [1, 3].includes(parseInt(from.getData("drift_model"))) && from.show('drift_base_amount');
                                        from.show('drift_model');
                                        from.show('drift_value');
                                        break;
                                    case 2:
                                        from.hide('exchange_rate');
                                        from.hide('keep_decimals');
                                        from.hide('drift_base_amount');
                                        from.hide('drift_model');
                                        from.hide('drift_value');
                                        break;
                                }
                            },
                            complete: (from, val) => {
                                from.form["sync_amount"].change(from, val);
                            }
                        },
                        {
                            title: "货币汇率",
                            name: "exchange_rate",
                            type: "number",
                            default: "0",
                            required: true,
                            hide: true,
                            tips: "如果对方货币是人民币，填0即可，如果是非人民币，则填写对方货币转人民币的汇率\n\n具体的计算方式：<b class='text-danger'>对方货币</b>÷<b class='text-success'>货币汇率</b>=<b class='text-primary'>人民币</b>\n\n<b class='text-warning'>注意：如果对方是人民币，填'0'即可，无需关心汇率问题</b>".replaceAll("\n", "<br>")
                        },
                        {
                            title: "保留小数",
                            name: "keep_decimals",
                            type: "input",
                            default: "2",
                            required: true,
                            hide: true,
                            placeholder: "请输入要保留的小数位数",
                            tips: "价格小数，最大支持6位小数"
                        },
                        {
                            title: "加价模式",
                            name: "drift_model",
                            type: "radio",
                            hide: true,
                            tips: format.success("比例向上/向下浮动") + " 如果你的商品是10元，那么【浮动值】设置 0.5，那么10元的商品最终售卖的价格就是：15【算法：10+(10*0.5)】<br>" + format.warning("固定金额向上/向下浮动") + " 通过基数+固定金额算法，得到的绝对比例进行加价，假如基数是10，加价1.2元，那么算法得出加价比例为：1.2÷10=0.12(12%)，假设一个商品为18元，最终售卖价格则是：20.16【算法：18+(18*0.12)】<br><br>注意：如果是向下浮动，就是把加法变成减法",
                            dict: "markup_type",
                            change: (form, val) => {
                                if (val == 1 || val == 3) {
                                    form.show('drift_base_amount');
                                } else {
                                    form.hide('drift_base_amount');
                                }
                            }
                        },
                        {
                            title: "价格基数",
                            name: "drift_base_amount",
                            tips: "基数就是你随便设定一个商品的成本价，比如你想象一个商品的成本价是10元，那么你就把基数设定为10元。<br><br>为什么要有这个设定呢？因为每个商品都有不同的类型和价格，设定一个基数可以帮助我们计算出你想给某个商品增加的价格。通过基数，我们可以简单地推算出商品的最终价格。",
                            type: "input",
                            placeholder: "请设定一个基数",
                            default: 10,
                            required: true,
                            hide: assign?.sync_amount != 1 || assign?.drift_model == 0 || assign?.drift_model == 2,
                            regex: {
                                value: "^(0\\.\\d+|[1-9]\\d*(\\.\\d+)?)$", message: "基数必须大于0"
                            }
                        },
                        {
                            title: "浮动值",
                            name: "drift_value",
                            type: "input",
                            tips: "【固定金额浮动模式】下填写具体金额<br><br>【比例浮动模式】下填写百分比，用小数代替，比如 10% 用小数表示就是 0.1，填写 0.1 即可",
                            placeholder: "请设置浮动值",
                            default: 1,
                            required: true,
                            hide: true,
                            regex: {
                                value: "^(0\\.\\d+|[0-9]\\d*(\\.\\d+)?)$", message: "浮动值必须是数字 "
                            }
                        },
                        {
                            title: false,
                            name: "info_module",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(`<div class="module-header">${i18n('商品信息同步')}</div>`);
                            }
                        },
                        {
                            title: "商品名称",
                            name: "sync_name",
                            type: "switch",
                            placeholder: "同步|不同步"
                        },
                        {
                            title: "商品介绍",
                            name: "sync_introduce",
                            type: "switch",
                            placeholder: "同步|不同步"
                        },
                        {
                            title: "封面图片",
                            name: "sync_picture",
                            type: "switch",
                            placeholder: "同步|不同步"
                        },
                        {
                            title: "SKU名称",
                            name: "sync_sku_name",
                            type: "switch",
                            placeholder: "同步|不同步"
                        },
                        {
                            title: "SKU封面",
                            name: "sync_sku_picture",
                            type: "switch",
                            placeholder: "同步|不同步"
                        },
                        {
                            title: "远程图片本地化",
                            name: "sync_remote_download",
                            type: "switch",
                            placeholder: "开启|关闭"
                        },
                    ]
                }
            ],
            assign: assign,
            autoPosition: true,
            done: () => {
                table.refresh();
            }
        });
    }

    table = new Table("/admin/repertory/item/markup/get", "#repertory-markup-table");
    table.setDeleteSelector(".del-repertory-markup", "/admin/repertory/item/markup/del");
    table.setUpdate("/admin/repertory/item/markup/save");
    table.setPagination(15, [15, 20, 30, 50, 100]);
    table.setColumns([
        {checkbox: true},
        {field: 'user', title: '商户', formatter: format.user},
        {field: 'name', title: '模板名称'},
        {
            field: 'sync_amount', title: '同步模式', dict: [
                {id: 0, name: "🚫不同步"},
                {id: 1, name: "💲同步并自定义价格"},
                {id: 2, name: "♻️同步上游"}
            ], text: "同步|不同步", reload: true, align: `center`
        },
        {
            field: 'drift_model', title: '加价模式', width: 170, formatter: (val, item) => {
                if (item.sync_amount != 1) {
                    return '-';
                }
                return _Dict.result('markup_type', val);
            }
        },
        {
            field: 'drift_value', title: '绝对比例', width: 120, formatter: (val, item) => {
                if (item.sync_amount != 1) {
                    return '-';
                }

                switch (item.drift_model) {
                    case 0:
                        return util.icon("icon-shangzhang") + (new Decimal(val)).mul(100).getAmount() + "%";
                    case 1:
                        return util.icon("icon-shangzhang") + (new Decimal(val)).div(item.drift_base_amount).mul(100).getAmount() + "%";
                    case 2:
                        return util.icon("icon-xiajiang") + (new Decimal(val)).mul(100).getAmount() + "%";
                    case 3:
                        return util.icon("icon-xiajiang") + (new Decimal(val)).div(item.drift_base_amount).mul(100).getAmount() + "%";
                }

                return '-';
            }
        },
        {
            field: 'drift_base_amount', title: '基数', width: 120, formatter: (val, item) => {
                if (item.sync_amount != 1 || item.drift_model == 0 || item.drift_model == 2) {
                    return '-';
                }
                return val;
            }
        },
        {
            field: 'keep_decimals', title: '保留小数', formatter: (val, item) => {
                if (item.sync_amount != 1) {
                    return '-';
                }
                return val;
            }
        },
        {field: 'sync_name', title: '商品名称', type: 'switch', text: "同步|不同步", reload: true},
        {field: 'sync_introduce', title: '商品介绍', type: 'switch', text: "同步|不同步", reload: true},
        {field: 'sync_picture', title: '商品封面', type: 'switch', text: "同步|不同步", reload: true},
        {field: 'sync_sku_name', title: 'SKU名称', type: 'switch', text: "同步|不同步", reload: true},
        {field: 'sync_sku_picture', title: 'SKU封面', type: 'switch', text: "同步|不同步", reload: true},
        {field: 'sync_remote_download', title: '图片本地化', type: 'switch', text: "开启|关闭", reload: true},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    tips: "修改模版",
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-bianji") + " 修改模板", row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    tips: '删除模版',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/repertory/item/markup/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.setSearch([
        {
            title: "商家，默认主站",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=2"
        },
        {title: "模板名称(模糊搜索)", name: "search-name", type: "input"},
    ]);
    table.render();


    $('.add-repertory-markup').click(() => {
        modal(util.icon("icon-tianjia") + " 添加模板");
    });
}();