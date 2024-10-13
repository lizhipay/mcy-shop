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
                            type: "switch",
                            placeholder: "同步|不同步"
                        },
                        {
                            title: "货币汇率",
                            name: "exchange_rate",
                            type: "number",
                            default: "0",
                            required: true,
                            tips: "如果对方货币是人民币，填0即可，如果是非人民币，则填写对方货币转人民币的汇率\n\n具体的计算方式：<b class='text-danger'>对方货币</b>÷<b class='text-success'>货币汇率</b>=<b class='text-primary'>人民币</b>\n\n<b class='text-warning'>注意：如果对方是人民币，填'0'即可，无需关心汇率问题</b>".replaceAll("\n", "<br>")
                        },
                        {
                            title: "保留小数",
                            name: "keep_decimals",
                            type: "input",
                            default: "2",
                            required: true,
                            tips: "最大支持6位小数"
                        },
                        {
                            title: "价格基数",
                            name: "drift_base_amount",
                            tips: "基数就是你随便设定一个商品的成本价，比如你想象一个商品的成本价是10元，那么你就把基数设定为10元。<br><br>为什么要有这个设定呢？因为每个商品都有不同的类型和价格，设定一个基数可以帮助我们计算出你想给某个商品增加的价格。通过基数，我们可以简单地推算出商品的最终价格。",
                            type: "input",
                            placeholder: "请设定一个基数",
                            default: 10,
                            required: true,
                            regex: {
                                value: "^(0\\.\\d+|[1-9]\\d*(\\.\\d+)?)$", message: "基数必须大于0"
                            }
                        },
                        {
                            title: "加价模式",
                            name: "drift_model",
                            type: "radio",
                            tips: format.success("比例加价") + " 通过基数实现百分比加价，比如你设置基数为10，那么比例设置 0.5，那么10元的商品最终售卖的价格就是：15【算法：(10*0.5)+10】<br>" + format.warning("固定金额加价") + " 通过基数+固定金额算法，得到的比例进行加价，假如基数是10，加价1.2元，那么算法得出加价比例为：1.2/10=0.12，如果一个商品为18元，你加价了1.2元，最终售卖价格则是：20.16【算法：(18*0.12)+18】",
                            dict: "markup_type"
                        },
                        {
                            title: "浮动值",
                            name: "drift_value",
                            type: "input",
                            tips: "百分比 或 金额，根据浮动类型自行填写，百分比需要用小数表示",
                            placeholder: "请设置浮动值",
                            default: 1,
                            required: true,
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
        {field: 'sync_amount', title: '同步价格', type: 'switch', text: "同步|不同步", reload: true},
        {field: 'drift_model', title: '加价模式', dict: "markup_type", width: 170},
        {field: 'drift_value', title: '加价比例/金额', type: 'text', width: 120, reload: true},
        {field: 'drift_base_amount', title: '基数', type: 'text', width: 120, reload: true},
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