!function () {
    let table, skuTable, skuGroupTable, tempId, skuTempId, skuUserTable, skuWholesaleTable, shipForm = [], shipName;

    const modal = (title, assign = {}) => {

        tempId = util.generateRandStr(16);

        let tabs = [
            {
                name: title,
                form: [
                    {
                        title: "sku_temp_id",
                        name: "sku_temp_id",
                        type: "input",
                        placeholder: "sku_temp_id",
                        hide: true,
                        default: tempId
                    },
                    {
                        title: "自动入库直营店",
                        name: "direct_sale",
                        type: "switch",
                        placeholder: "自动入库|不自动入库",
                        default: 1,
                        tips: "开启此选项后，商品将直接入库至直营店，并在网站首页以可购买状态展示",
                        change: (form, val) => {
                            if (val == 1) {
                                form.show('direct_category_id');
                            } else {
                                form.hide('direct_category_id');
                            }
                        },
                        hide: assign?.id > 0
                    },
                    {
                        title: "直营店分类",
                        name: "direct_category_id",
                        type: "treeSelect",
                        placeholder: "请选择直营店的商品分类",
                        dict: 'shopCategory?userId=',
                        parent: false,
                        hide: assign?.id > 0
                    },
                    {
                        title: "仓库分类",
                        name: "repertory_category_id",
                        type: "select",
                        placeholder: "请选择仓库分类",
                        dict: 'repertoryCategory',
                        regex: {
                            value: "^[1-9]\\d*$",
                            message: "必须选中一个分类"
                        },
                        required: true
                    },
                    {
                        title: "商品名称",
                        name: "name",
                        type: "textarea",
                        height: 34,
                        placeholder: "请输入商品名称，支持自定义HTML美化",
                        picker: true,
                        required: true
                    },
                    {
                        title: "商品封面",
                        name: "picture_url",
                        type: "image",
                        placeholder: "请选择封面图片",
                        uploadUrl: '/admin/upload?thumb_height=128',
                        photoAlbumUrl: '/admin/upload/get',
                        height: 300,
                        change: (form, url, data) => {
                            form.setInput("picture_thumb_url", data.append.thumb_url);
                            message.success("缩略图已生成");
                        },
                        required: true
                    },
                    {
                        title: "缩略图",
                        name: "picture_thumb_url",
                        type: "input",
                        hide: true
                    },
                    {
                        title: "排序",
                        name: "sort",
                        type: "input",
                        placeholder: "排序，越小越靠前",
                        default: 0,
                        tips: "数值越小，商品排名越靠前"
                    },
                    {
                        title: "状态",
                        name: "status",
                        type: "select",
                        dict: "repertory_item_status",
                        placeholder: "请选择状态",
                        default: 1
                    },
                    {
                        title: "对接权限",
                        name: "privacy",
                        type: "select",
                        placeholder: "请选择对接权限",
                        default: 0,
                        dict: "repertory_item_privacy"
                    },
                    {
                        title: "退款方式",
                        name: "refund_mode",
                        type: "select",
                        placeholder: "请选择退款方式",
                        default: 0,
                        dict: "repertory_item_refund_mode",
                        tips: `
                        1.不支持退款：商品被购买，没有任何退款渠道
                        2.有条件退款：商品被购买，资金即时结算，就算退款，涉及的分红资金也不予回滚，供货商保留对退款金额进行调整的权利，确保双方权益得到合理处理。
                        3.无理由退款：根据商品设置的资金冻结期限，所有与订单相关的资金将被冻结，只有等到解冻时间后，才可以使用这部分资金。`.trim().replaceAll("\n", "<br><br>")
                    },
                    {
                        title: "自动收货时效",
                        name: "auto_receipt_time",
                        type: "input",
                        placeholder: "自动收货时效",
                        default: 5040,
                        tips: "自动收货时效，单位/分钟，如果为'0'的情况下，货源会发货并且立即收货，不需要经过顾客同意"
                    },
                ]
            },
            {
                name: util.icon("icon-shuoming") + "<space></space>商品介绍",
                form: [
                    {
                        name: "introduce",
                        uploadUrl: "/admin/upload",
                        photoAlbumUrl: '/admin/upload/get',
                        type: "editor",
                        placeholder: "介绍一下你的商品信息吧",
                        height: 660,
                        required: true
                    },
                ]
            },
            {
                name: util.icon("icon-fahuo") + "<space></space>发货插件",
                form: [
                    {
                        title: "发货插件",
                        name: "plugin",
                        type: "select",
                        required: true,
                        dict: "ship",
                        change: (popup, val) => {
                            shipForm.forEach(form => {
                                popup.removeForm(form.name);
                            });
                            shipName = val;
                            if (val == "" || val == null) {
                                return;
                            }
                            util.post({
                                url: "/admin/plugin/submit/js?name=" + val + "&js=Item.Form",
                                done: res => {
                                    if (!res?.data?.code) {
                                        return;
                                    }
                                    let data = eval('(' + res.data.code + ')');
                                    if (data == "") {
                                        return;
                                    }
                                    shipForm = data;
                                    data.forEach(form => {
                                        if (assign.hasOwnProperty(form.name)) {
                                            form.default = assign[form.name];
                                        }
                                        popup.createForm(form, "plugin", "after");
                                    });
                                }
                            });
                        },
                        complete: (popup, val) => {
                            popup.form["plugin"].change(popup, val);
                        }
                    }
                ]
            },
            {
                name: util.icon("icon-icon-shurukuang") + "<space></space>控件",
                form: [
                    {
                        name: "widget",
                        type: "widget",
                        height: 660
                    },
                ]
            },
            {
                name: util.icon("icon-a-shuxing1x") + "<space></space>商品属性",
                form: [
                    {
                        name: "attr",
                        type: "attribute",
                        height: 660
                    },
                ]
            },
            {
                name: util.icon("icon-tubiaoguifan-09") + "<space></space>SKU",
                form: [
                    {
                        name: "sku",
                        type: "custom",
                        complete: (popup, dom) => {

                            dom.html(`<div class="block block-rounded">
        <div class="block-header block-header-default">
            <button type="button" class="btn btn-outline-success btn-sm add-repertory-itemSku">${util.icon("icon-tianjia")}<space></space>${i18n("添加SKU")}
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm del-repertory-itemSku">${util.icon("icon-shanchu")}<space></space>${i18n("移除SKU")}
            </button>
        </div>
        <div class="block-content pt-0">
            <table id="repertory-itemSku-table"></table>
        </div>
    </div>`);

                            let columns = [
                                {checkbox: true},
                                {field: 'name', title: 'SKU名称', class: "nowrap", formatter: format.item},
                                {
                                    field: 'stock_price',
                                    title: '进货价',
                                    type: 'input',
                                    reload: true,
                                    width: 95,
                                    formatter: format.amountRemoveTrailingZeros
                                },

                            ];

                            if (assign.user_id > 0) {
                                columns.push({
                                    field: 'supply_price',
                                    title: '成本',
                                    width: 95,
                                    formatter: format.amountRemoveTrailingZeros
                                });
                            } else {
                                columns.push({
                                    field: 'cost',
                                    title: '成本',
                                    type: 'input',
                                    reload: true,
                                    width: 95,
                                    formatter: format.amountRemoveTrailingZeros
                                });
                            }

                            columns = columns.concat([
                                {
                                    field: 'market_control_status',
                                    title: '控制市场',
                                    class: "nowrap",
                                    type: 'button',
                                    width: 110,
                                    buttons: [
                                        {
                                            icon: 'icon-shezhi3',
                                            title: '设置',
                                            class: 'btn-table-success',
                                            click: (event, value, row, index) => {
                                                marketControlModal("/admin/repertory/item/sku/save", row, skuTable);
                                            }
                                        }
                                    ]
                                },
                                {field: 'sort', title: '排序', type: 'input', reload: true, width: 75},
                                {
                                    field: 'wholesale',
                                    title: '批发',
                                    class: "nowrap",
                                    type: 'button',
                                    width: 110,
                                    buttons: [
                                        {
                                            icon: 'icon-shezhi',
                                            title: '配置',
                                            class: 'acg-badge-h-setting',
                                            click: (event, value, row, index) => {
                                                wholesaleModal(util.icon("icon-jiajushebeipiliangbanqianshenqingbiao") + "<space></space>批发设置", row.id);
                                            }
                                        }
                                    ]
                                },
                                {
                                    field: 'private_display',
                                    title: '私密模式',
                                    class: "nowrap",
                                    type: 'switch',
                                    text: "ON|OFF",
                                    reload: true
                                },
                                {
                                    field: 'operation',
                                    title: '操作',
                                    class: "nowrap",
                                    type: 'button',
                                    width: 110,
                                    buttons: [
                                        {
                                            icon: 'icon-biaoge-xiugai',
                                            class: 'acg-badge-h-dodgerblue',
                                            click: (event, value, row, index) => {
                                                skuModal(util.icon("icon-biaoge-xiugai") + " 修改SKU", row, {popup: popup});
                                            }
                                        },
                                        {
                                            icon: 'icon-shanchu1',
                                            class: 'acg-badge-h-red',
                                            click: (event, value, row, index) => {
                                                component.deleteDatabase("/admin/repertory/item/sku/del", [row.id], () => {
                                                    skuTable.refresh();
                                                });
                                            }
                                        }
                                    ]
                                },
                            ]);

                            skuTable = new Table(
                                "/admin/repertory/item/sku/get?id=" + (assign.id ?? tempId) + "&type=" + (assign.id ? "edit" : "add"),
                                dom.find('#repertory-itemSku-table')
                            );
                            skuTable.setColumns(columns);
                            skuTable.setUpdate("/admin/repertory/item/sku/save");
                            skuTable.setDeleteSelector(".del-repertory-itemSku", "/admin/repertory/item/sku/del");
                            skuTable.render();

                            $('.add-repertory-itemSku').click(() => {
                                let skuAssign = {
                                    temp_id: tempId,
                                    repertory_item_id: assign.id
                                }
                                skuModal(util.icon("icon-tianjia") + '<space></space>添加SKU', skuAssign, {popup: popup});
                            });
                        }
                    },
                ]
            }
        ];

        if (assign?.markup_mode > 0) {
            tabs.push({
                name: util.icon("icon-jiage") + " 远程同步配置",
                form: [
                    {
                        title: "配置模式",
                        name: "markup_mode",
                        type: "radio",
                        tips: "自定义配置：每个货源独立自定义配置<br>模板配置：选择一个创建好的模板，由模板统一管理价格盈亏",
                        dict: [
                            {id: 1, name: format.danger('自定义配置')},
                            {id: 2, name: format.success('模板配置')}
                        ],
                        change: (obj, value) => {
                            if (value == 1) {
                                obj.hide("markup_template_id");
                                // obj.show("markup.drift_base_amount");
                                // obj.show("markup.drift_model");
                                //  obj.show("markup.drift_value");
                                obj.show("markup.sync_name");
                                obj.show("markup.sync_introduce");
                                obj.show("markup.sync_picture");
                                obj.show("markup.sync_sku_name");
                                obj.show("markup.sync_sku_picture");
                                obj.show("markup.sync_amount");
                                obj.show("price_module");
                                obj.show("info_module");
                                obj.show("markup.sync_remote_download");
                                //    obj.show("markup.exchange_rate");
                                //     obj.show("markup.keep_decimals");
                                obj.setRadio("markup.sync_amount", 0, true);
                            } else {
                                obj.show("markup_template_id");
                                obj.hide("markup.drift_base_amount");
                                obj.hide("markup.drift_model");
                                obj.hide("markup.drift_value");
                                obj.hide("markup.sync_name");
                                obj.hide("markup.sync_introduce");
                                obj.hide("markup.sync_picture");
                                obj.hide("markup.sync_sku_name");
                                obj.hide("markup.sync_sku_picture");
                                obj.hide("markup.sync_amount");
                                obj.hide("price_module");
                                obj.hide("info_module");
                                obj.hide("markup.sync_remote_download");
                                obj.hide("markup.exchange_rate");
                                obj.hide("markup.keep_decimals");
                            }
                        },
                        complete: (obj, value) => {
                            obj.triggerOtherPopupChange("markup_mode", value);
                        }
                    },
                    {
                        title: "配置模板",
                        name: "markup_template_id",
                        type: "select",
                        tips: "如果这里没有模板，请先到同步模板中进行新增",
                        placeholder: "请选择模板",
                        dict: "repertoryItemMarkupTemplate?userId=" + assign.user_id
                    },
                    {
                        title: false,
                        name: "price_module",
                        type: "custom",
                        complete: (form, dom) => {
                            dom.html(`<div class="module-header">同步价格模块</div>`);
                        }
                    },
                    {
                        title: "同步价格",
                        name: "markup.sync_amount",
                        type: "radio",
                        placeholder: "同步|不同步",
                        dict: [
                            {id: 0, name: "不同步"},
                            {id: 1, name: "同步上游并加价"},
                            {id: 2, name: "同步上游"}
                        ],
                        required: true,
                        tips: "不同步：完全由本地自定义价格\n同步并加价：根据上游的商品价格实时控制盈亏\n同步上游：上游是什么价格，本地商品就是什么价格".replaceAll("\n", "<br>"),
                        change: (from, val) => {
                            val = parseInt(val);
                            switch (val) {
                                case 0:
                                    from.hide('markup.exchange_rate');
                                    from.hide('markup.keep_decimals');
                                    from.hide('markup.drift_base_amount');
                                    from.hide('markup.drift_model');
                                    from.hide('markup.drift_value');
                                    break;
                                case 1:
                                    from.show('markup.exchange_rate');
                                    from.show('markup.keep_decimals');
                                    from.show('markup.drift_base_amount');
                                    from.show('markup.drift_model');
                                    from.show('markup.drift_value');
                                    break;
                                case 2:
                                    from.hide('markup.exchange_rate');
                                    from.hide('markup.keep_decimals');
                                    from.hide('markup.drift_base_amount');
                                    from.hide('markup.drift_model');
                                    from.hide('markup.drift_value');
                                    break;
                            }
                        },
                        complete: (obj, value) => {
                            obj.triggerOtherPopupChange("markup.sync_amount", value);
                        }
                    },
                    {
                        title: "货币汇率",
                        name: "markup.exchange_rate",
                        type: "number",
                        default: "0",
                        required: true,
                        hide: true,
                        tips: "如果对方货币是人民币，填0即可，如果是非人民币，则填写对方货币转人民币的汇率\n\n具体的计算方式：<b class='text-danger'>对方货币</b>÷<b class='text-success'>货币汇率</b>=<b class='text-primary'>人民币</b>\n\n<b class='text-warning'>注意：如果对方是人民币，填'0'即可，无需关心汇率问题</b>".replaceAll("\n", "<br>")
                    },
                    {
                        title: "保留小数",
                        name: "markup.keep_decimals",
                        type: "input",
                        default: "2",
                        required: true,
                        hide: true,
                        placeholder: "请输入要保留的小数位数",
                        tips: "价格小数，最大支持6位小数"
                    },
                    {
                        title: "价格基数",
                        name: "markup.drift_base_amount",
                        type: "input",
                        tips: "基数就是你随便设定一个商品的进货价，比如你想象一个商品的进货价是10元，那么你就把基数设定为10元。<br><br>为什么要有这个设定呢？因为每个商品都有不同的类型和价格，设定一个基数可以帮助我们计算出你想给某个商品增加的进货价。通过基数，我们可以简单地推算出商品的最终进货价。",
                        placeholder: "请设定基数",
                        default: 10,
                        hide: true,
                        required: true,
                        regex: {
                            value: "^(0\\.\\d+|[1-9]\\d*(\\.\\d+)?)$", message: "基数必须大于0"
                        }
                    },
                    {
                        title: "加价模式",
                        name: "markup.drift_model",
                        type: "radio",
                        hide: true,
                        tips: format.success("比例加价") + " 通过基数实现百分比加价，比如你设置基数为10，那么比例设置 0.5，那么10元的商品最终售卖的价格就是：15【算法：(10*0.5)+10】<br>" + format.warning("固定金额加价") + " 通过基数+固定金额算法，得到的比例进行加价，假如基数是10，加价1.2元，那么算法得出加价比例为：1.2/10=0.12，如果一个商品为18元，你加价了1.2元，最终售卖价格则是：20.16【算法：(18*0.12)+18】",
                        dict: "markup_type"
                    },
                    {
                        title: "浮动值",
                        name: "markup.drift_value",
                        type: "input",
                        tips: "百分比 或 金额，根据加价模式自行填写，百分比需要用小数表示",
                        placeholder: "请设置浮动值",
                        default: 0,
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
                            dom.html(`<div class="module-header">商品信息同步</div>`);
                        }
                    },
                    {
                        title: "商品名称",
                        name: "markup.sync_name",
                        type: "switch",
                        placeholder: "同步|不同步"
                    },
                    {
                        title: "商品介绍",
                        name: "markup.sync_introduce",
                        type: "switch",
                        placeholder: "同步|不同步"
                    },
                    {
                        title: "封面图片",
                        name: "markup.sync_picture",
                        type: "switch",
                        placeholder: "同步|不同步"
                    },
                    {
                        title: "SKU名称",
                        name: "markup.sync_sku_name",
                        type: "switch",
                        placeholder: "同步|不同步"
                    },
                    {
                        title: "SKU封面",
                        name: "markup.sync_sku_picture",
                        type: "switch",
                        placeholder: "同步|不同步"
                    },
                    {
                        title: "图片本地化",
                        name: "markup.sync_remote_download",
                        type: "switch",
                        placeholder: "开启|不开启"
                    },
                ]
            });
        }

        if (assign.user_id > 0) {
            tabs.splice(2, 1);
        }

        if (!util.isObjectEmpty(getVar("hookItemPopup"))) {
            tabs = tabs.concat(getVar("hookItemPopup"));
        }

        component.popup({
            submit: '/admin/repertory/item/save',
            tab: tabs,
            assign: assign,
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            width: "1100px",
            done: () => {
                table.refresh();
            }
        });
    }
    const skuModal = (title, assign = {}, item = {}) => {
        skuTempId = util.generateRandStr(16);

        let tabs = [
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
                        name: "sku_temp_id",
                        type: "input",
                        hide: true,
                        default: skuTempId
                    },
                    {
                        title: "repertory_item_id",
                        name: "repertory_item_id",
                        type: "input",
                        placeholder: "repertory_item_id",
                        hide: true
                    },
                    {
                        title: "SKU封面",
                        name: "picture_url",
                        type: "image",
                        placeholder: "请选择图片",
                        uploadUrl: '/admin/upload?thumb_height=128',
                        photoAlbumUrl: '/admin/upload/get',
                        height: 300,
                        change: (form, url, data) => {
                            form.setInput("picture_thumb_url", data.append.thumb_url);
                            message.success("缩略图已生成");
                        },
                        required: true
                    },
                    {
                        title: "缩略图",
                        name: "picture_thumb_url",
                        type: "input",
                        hide: true
                    },
                    {
                        title: "SKU名称",
                        name: "name",
                        type: "input",
                        placeholder: "请输入SKU名称",
                        picker: true,
                        required: true
                    },
                    {
                        title: "进货价",
                        name: "stock_price",
                        type: "number",
                        placeholder: "进货价",
                        tips: "【进货价】是商家拿货的价格",
                        required: true
                    }, {
                        title: "成本",
                        name: "cost",
                        type: "number",
                        placeholder: "成本",
                        default: 0,
                        tips: "【成本】是用来为您计算盈利和盈亏的重要数据，请务必提供真实数据",
                        hide: assign.user_id > 0
                    },
                    {
                        title: "排序",
                        name: "sort",
                        type: "number",
                        placeholder: "排序，越小越靠前",
                        default: 0,
                        tips: "数值越小，商品排名越靠前"
                    },
                    {
                        title: "私密",
                        name: "private_display",
                        type: "switch",
                        tips: "启用私密模式后，只有设置过独立显示的【商家权限组】或【分站】才可以看到该SKU，如该货源没有任何SKU可以购买，货源则会完全隐藏。"
                    }
                ]
            },
            {
                name: util.icon("icon-duanxinpeizhi") + " 发货留言",
                form: [
                    {
                        name: "message",
                        type: "editor",
                        placeholder: "发货后给用户的留言",
                        tips: "当商品发货后，此留言会展示给用户看",
                        uploadUrl: "/admin/upload",
                        photoAlbumUrl: '/admin/upload/get',
                        height: 360
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
                            const updateUrl = "/admin/repertory/item/sku/group/save?skuId=" + (assign.id ?? skuTempId) + "&type=" + (assign.id ? "real" : "temp");

                            dom.html(`<div class="block block-rounded"> <div class="block-content mt-0 pt-0"><table id="repertory-item-sku-group-table"></table> </div> </div>`);
                            skuGroupTable = new Table("/admin/repertory/item/sku/group/get?id=" + (assign.id ?? skuTempId) + "&type=" + (assign.id ? "real" : "temp"), dom.find('#repertory-item-sku-group-table'));

                            skuGroupTable.setColumns([
                                {
                                    field: 'name', title: '用户组', formatter: (name, item) => {
                                        return format.group(item);
                                    }
                                },
                                {
                                    field: 'stock_price',
                                    title: '进货价',
                                    type: 'input',
                                    width: 95,
                                    formatter: format.amountRemoveTrailingZeros
                                },
                                {
                                    field: 'market_control_status',
                                    title: '控制市场',
                                    class: "nowrap",
                                    type: 'button',
                                    width: 110,
                                    buttons: [
                                        {
                                            icon: 'icon-shezhi3',
                                            title: '设置',
                                            class: 'btn-table-success',
                                            click: (event, value, row, index) => {
                                                marketControlUerOrGroupModal(updateUrl, row, skuGroupTable);
                                            }
                                        }
                                    ]
                                },
                                {
                                    field: 'status',
                                    title: '状态',
                                    type: 'switch',
                                    text: "启用|关闭",
                                    width: 100
                                }
                            ]);

                            skuGroupTable.setUpdate(updateUrl);
                            skuGroupTable.disablePagination();
                            skuGroupTable.render();
                        }
                    }
                ]
            }, {
                name: util.icon("icon-yonghu") + " 分站",
                form: [
                    {
                        name: "user",
                        type: "custom",
                        complete: (popup, dom) => {
                            const updateUrl = "/admin/repertory/item/sku/user/save?skuId=" + (assign.id ?? skuTempId) + "&type=" + (assign.id ? "real" : "temp");
                            dom.html(`<div class="block block-rounded"><div class="block-content mt-0 pt-0"><table id="repertory-item-sku-user-table"></table></div></div>`);
                            skuUserTable = new Table("/admin/repertory/item/sku/user/get?id=" + (assign.id ?? skuTempId) + "&type=" + (assign.id ? "real" : "temp"), dom.find('#repertory-item-sku-user-table'));
                            skuUserTable.setUpdate(updateUrl);
                            skuUserTable.setColumns([
                                {
                                    field: 'username', title: '商家', formatter: function (val, item) {
                                        return format.user(item);
                                    }
                                },
                                {
                                    field: 'stock_price',
                                    title: '进货价',
                                    type: 'input',
                                    width: 95,
                                    formatter: format.amountRemoveTrailingZeros
                                },
                                {
                                    field: 'market_control_status',
                                    title: '控制市场',
                                    class: "nowrap",
                                    type: 'button',
                                    width: 110,
                                    buttons: [
                                        {
                                            icon: 'icon-shezhi3',
                                            title: '设置',
                                            class: 'btn-table-success',
                                            click: (event, value, row, index) => {
                                                marketControlUerOrGroupModal(updateUrl, row, skuUserTable);
                                            }
                                        }
                                    ]
                                },
                                {
                                    field: 'status',
                                    title: '状态',
                                    type: 'switch',
                                    text: "启用|关闭",
                                    width: 100
                                }
                            ]);
                            skuUserTable.setSearch([
                                {title: "ID", name: "equal-id", type: "input", width: 90},
                                {title: "用户名", name: "equal-username", type: "input", width: 125},
                                {title: "备注", name: "search-note", type: "input", width: 125}
                            ]);
                            skuUserTable.render();
                            //-------------
                        }
                    }
                ]
            },
        ];

        if (assign.user_id != undefined) {
            component.popup({
                submit: '/admin/repertory/item/sku/save',
                tab: tabs,
                assign: assign,
                autoPosition: true,
                content: {
                    css: {
                        height: "auto",
                        overflow: "inherit"
                    }
                },
                height: "auto",
                width: "780px",
                done: () => {
                    skuTable.refresh();
                }
            });
            return;
        }


        if (!shipName) {
            message.error("请选择发货插件，在添加/修改SKU");
            return;
        }

        util.post({
            url: "/admin/plugin/submit/js?name=" + shipName + "&js=Sku.Tab",
            done: res => {
                let tab = [];
                if (res.data.code != "") {
                    tab = eval('(' + res.data.code + ')');
                }

                tabs = tabs.concat(tab);
                component.popup({
                    submit: '/admin/repertory/item/sku/save',
                    tab: tabs,
                    assign: assign,
                    autoPosition: true,
                    content: {
                        css: {
                            height: "auto",
                            overflow: "inherit"
                        }
                    },
                    height: "auto",
                    width: "780px",
                    done: () => {
                        skuTable.refresh();
                    }
                });
            }
        });
    }
    const wholesaleModal = (title, skuId) => {
        component.popup({
            tab: [
                {
                    name: title,
                    form: [
                        {
                            name: "group",
                            type: "custom",
                            complete: (popup, dom) => {
                                dom.html(`<div class="block block-rounded"><div class="block-header block-header-default"><button type="button" class="btn btn-outline-success btn-sm add-repertory-item-sku-wholesale"><i class="mcy-icon icon-tianjia"></i>` + util.icon("icon-tianjia") + ` 添加规则</button><button type="button" class="btn btn-outline-danger btn-sm del-repertory-item-sku-wholesale"><i class="mcy-icon icon-shanchu"></i>` + util.icon("icon-shanchu") + ` 移除规则</button></div><div class="block-content"><table id="repertory-item-sku-wholesale-table"></table></div></div>`);

                                skuWholesaleTable = new Table("/admin/repertory/item/sku/wholesale/get?id=" + skuId, dom.find('#repertory-item-sku-wholesale-table'));
                                skuWholesaleTable.disablePagination();
                                skuWholesaleTable.setUpdate("/admin/repertory/item/sku/wholesale/save");
                                skuWholesaleTable.setDeleteSelector(".del-repertory-item-sku-wholesale", "/admin/repertory/item/sku/wholesale/del");
                                skuWholesaleTable.setColumns([
                                    {checkbox: true},
                                    {field: 'quantity', title: '数量', type: 'input', reload: true},
                                    {
                                        field: 'stock_price',
                                        title: '进货价',
                                        type: 'input',
                                        width: 95,
                                        formatter: format.amountRemoveTrailingZeros
                                    },
                                    {
                                        field: 'group', title: '用户组', type: 'button', width: 120, buttons: [
                                            {
                                                icon: "icon-xinjianyonghuzu",
                                                title: "配置",
                                                class: 'btn-outline-dodgerblue',
                                                click: (event, value, row, index) => {
                                                    //------------------------------
                                                    component.popup({
                                                        tab: [
                                                            {
                                                                name: util.icon("icon-jiajushebeipiliangbanqianshenqingbiao") + " 批发设置 -> [数量:" + row.quantity + "] -> 用户组",
                                                                form: [
                                                                    {
                                                                        name: "wholesale_group",
                                                                        type: "custom",
                                                                        complete: (popup, dom) => {
                                                                            dom.html(`<div class="block block-rounded"><div class="block-content mt-0 pt-0"><table id="repertory-item-sku-wholesale-group-table"></table></div></div>`);

                                                                            const table = new Table("/admin/repertory/item/sku/wholesale/group/get?wholesaleId=" + row.id, dom.find('#repertory-item-sku-wholesale-group-table'));
                                                                            table.disablePagination();
                                                                            table.setColumns([
                                                                                {
                                                                                    field: 'name',
                                                                                    title: '用户组',
                                                                                    formatter: (name, item) => {
                                                                                        return format.group(item);
                                                                                    }
                                                                                },
                                                                                {
                                                                                    field: 'stock_price',
                                                                                    title: '进货价',
                                                                                    type: 'input',
                                                                                    formatter: format.amountRemoveTrailingZeros,
                                                                                    width: 95
                                                                                },
                                                                                {
                                                                                    field: 'status',
                                                                                    title: '状态',
                                                                                    type: 'switch',
                                                                                    text: "启用|关闭",
                                                                                    width: 100,
                                                                                    reload: true
                                                                                }
                                                                            ]);
                                                                            table.setUpdate("/admin/repertory/item/sku/wholesale/group/save?wholesaleId=" + row.id);
                                                                            table.render();
                                                                            //-------------
                                                                        }
                                                                    }
                                                                ]
                                                            }
                                                        ],
                                                        autoPosition: true,
                                                        width: "480px"
                                                    });
                                                    //-----------------------------
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        field: 'user', title: '分站', type: 'button', width: 120, buttons: [
                                            {
                                                icon: "icon-yonghu",
                                                title: "配置",
                                                class: 'acg-badge-h-setting',
                                                click: (event, value, row, index) => {
                                                    //------------------------------
                                                    component.popup({
                                                        tab: [
                                                            {
                                                                name: util.icon("icon-yonghu") + " 批发设置 -> [数量:" + row.quantity + "] -> 商家",
                                                                form: [
                                                                    {
                                                                        name: "wholesale_user",
                                                                        type: "custom",
                                                                        complete: (popup, dom) => {
                                                                            dom.html(`<div class="block block-rounded"><div class="block-content mt-0 pt-0"><table id="repertory-item-sku-wholesale-user-table"></table></div></div>`);

                                                                            const table = new Table("/admin/repertory/item/sku/wholesale/user/get?wholesaleId=" + row.id, dom.find('#repertory-item-sku-wholesale-user-table'));
                                                                            table.setUpdate("/admin/repertory/item/sku/wholesale/user/save?wholesaleId=" + row.id);
                                                                            table.setColumns([
                                                                                {
                                                                                    field: 'username',
                                                                                    title: '商家',
                                                                                    formatter: function (val, item) {
                                                                                        return format.user(item);
                                                                                    }
                                                                                },
                                                                                {
                                                                                    field: 'stock_price',
                                                                                    title: '进货价',
                                                                                    type: 'input',
                                                                                    width: 95,
                                                                                    formatter: format.amountRemoveTrailingZeros
                                                                                },
                                                                                {
                                                                                    field: 'status',
                                                                                    title: '状态',
                                                                                    type: 'switch',
                                                                                    text: "启用|关闭",
                                                                                    width: 100,
                                                                                    reload: true
                                                                                }
                                                                            ]);
                                                                            table.setSearch([
                                                                                {
                                                                                    title: "ID",
                                                                                    name: "equal-id",
                                                                                    type: "input",
                                                                                    width: 90
                                                                                },
                                                                                {
                                                                                    title: "用户名",
                                                                                    name: "equal-username",
                                                                                    type: "input",
                                                                                    width: 125
                                                                                },
                                                                                {
                                                                                    title: "备注",
                                                                                    name: "search-note",
                                                                                    type: "input",
                                                                                    width: 125
                                                                                }
                                                                            ]);

                                                                            table.render();
                                                                            //-------------
                                                                        }
                                                                    }
                                                                ]
                                                            }
                                                        ],
                                                        autoPosition: true,
                                                        width: "580px"
                                                    });
                                                    //-----------------------------
                                                }
                                            }
                                        ]
                                    },
                                ]);
                                skuWholesaleTable.render();

                                $('.add-repertory-item-sku-wholesale').click(() => {
                                    component.popup({
                                        submit: '/admin/repertory/item/sku/wholesale/save',
                                        tab: [
                                            {
                                                name: title,
                                                form: [
                                                    {
                                                        title: "数量",
                                                        name: "quantity",
                                                        type: "number",
                                                        placeholder: "请输入批发数量",
                                                        tips: "商家一次性进货达到该数量后，就会使用当前设置的批发进货价格"
                                                    },
                                                    {
                                                        title: "进货价",
                                                        name: "stock_price",
                                                        type: "number",
                                                        placeholder: "请输入进货价(单价)"
                                                    },
                                                    {name: "sku_id", type: "input", default: skuId, hide: true},
                                                ]
                                            }
                                        ],
                                        height: "250px",
                                        width: "490px",
                                        done: () => {
                                            skuWholesaleTable.refresh();
                                        }
                                    });

                                });
                            }
                        }
                    ]
                }
            ],
            autoPosition: true,
            width: "580px"
        });
    }

    const marketControlModal = (url, assign = {}, $table = null) => {
        component.popup({
            submit: url,
            tab: [
                {
                    name: util.icon("icon-jiajushebeipiliangbanqianshenqingbiao") + " 控制市场",
                    form: [
                        {
                            title: "控制市场",
                            name: "market_control_status",
                            type: "switch",
                            placeholder: "启用|关闭",
                            tips: "启用后，市场零售价格将受到控制",
                            width: 90,
                            change: (form, value) => {
                                const list = [
                                    'market_control_min_price',
                                    'market_control_max_price',
                                    'market_control_min_num',
                                    'market_control_max_num',
                                    'market_control_only_num',
                                    'market_control_level_min_price',
                                    'market_control_level_max_price',
                                    'market_control_user_min_price',
                                    'market_control_user_max_price',
                                ];
                                if (value) {
                                    list.forEach(item => form.show(item));
                                } else {
                                    list.forEach(item => form.hide(item));
                                }
                            }
                        },
                        {
                            title: "游客最低价",
                            name: "market_control_min_price",
                            type: "number",
                            default: 0,
                            placeholder: "游客最低价",
                            tips: "游客最低价，商品的价格不得低于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        },
                        {
                            title: "游客最高价",
                            name: "market_control_max_price",
                            type: "number",
                            default: 0,
                            placeholder: "游客最高价",
                            tips: "市场最高价，商品的价格不得高于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        },
                        {
                            title: "会员等级最低价",
                            name: "market_control_level_min_price",
                            type: "number",
                            default: 0,
                            placeholder: "会员等级最低价",
                            tips: "会员等级最低价，商品的价格不得低于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        },
                        {
                            title: "会员等级最高价",
                            name: "market_control_level_max_price",
                            type: "number",
                            default: 0,
                            placeholder: "会员等级最高价",
                            tips: "会员等级最高价，商品的价格不得高于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        },
                        {
                            title: "会员密价最低价",
                            name: "market_control_user_min_price",
                            type: "number",
                            default: 0,
                            placeholder: "会员密价最低价",
                            tips: "会员密价最低价，商品的价格不得低于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        },
                        {
                            title: "会员密价最高价",
                            name: "market_control_user_max_price",
                            type: "number",
                            default: 0,
                            placeholder: "会员密价最高价",
                            tips: "会员密价最高价，商品的价格不得高于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        },
                        {
                            title: "单次最低购买数",
                            name: "market_control_min_num",
                            type: "number",
                            placeholder: "单次下单最低购买数量",
                            default: 0,
                            tips: "单次下单最低购买数量，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        },
                        {
                            title: "单次最多购买数",
                            name: "market_control_max_num",
                            type: "number",
                            placeholder: "单次下单最多购买数量",
                            default: 0,
                            tips: "单次下单最多购买数量，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        },
                        {
                            title: "每人最多购买数",
                            name: "market_control_only_num",
                            type: "number",
                            default: 0,
                            placeholder: "每个人最多购买同一个SKU数量，超过则无法再进行购买",
                            tips: "每个人最多购买同一个SKU数量，超过则无法再进行购买，如果为'0'代表不限制",
                            hide: assign.market_control_status == 0 ?? true
                        }
                    ]
                }
            ],
            assign: assign,
            autoPosition: true,
            width: "460px",
            done: () => {
                $table && $table.refresh();
            }
        });
    }

    const marketControlUerOrGroupModal = (url, assign = {}, $table = null) => {
        component.popup({
            submit: url,
            tab: [
                {
                    name: util.icon("icon-jiajushebeipiliangbanqianshenqingbiao") + " 控制市场",
                    form: [
                        {
                            title: "控制市场",
                            name: "market_control_status",
                            type: "radio",
                            dict: [
                                {id: 0, name: "同步全局"},
                                {id: 1, name: "自定义"},
                                {id: 2, name: "不限制"}
                            ],
                            change: (form, value) => {
                                const list = [
                                    'market_control_min_price',
                                    'market_control_max_price',
                                    'market_control_min_num',
                                    'market_control_max_num',
                                    'market_control_only_num',
                                    'market_control_level_min_price',
                                    'market_control_level_max_price',
                                    'market_control_user_min_price',
                                    'market_control_user_max_price',
                                ];
                                if (value == 1) {
                                    list.forEach(item => form.show(item));
                                } else {
                                    list.forEach(item => form.hide(item));
                                }
                            },
                            default: 0
                        },
                        {
                            title: "游客最低价",
                            name: "market_control_min_price",
                            type: "number",
                            default: 0,
                            placeholder: "游客最低价",
                            tips: "游客最低价，商品的价格不得低于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        },
                        {
                            title: "游客最高价",
                            name: "market_control_max_price",
                            type: "number",
                            default: 0,
                            placeholder: "游客最高价",
                            tips: "市场最高价，商品的价格不得高于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        },
                        {
                            title: "会员等级最低价",
                            name: "market_control_level_min_price",
                            type: "number",
                            default: 0,
                            placeholder: "会会员等级最低价",
                            tips: "会员等级最低价，商品的价格不得低于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        },
                        {
                            title: "会员等级最高价",
                            name: "market_control_level_max_price",
                            type: "number",
                            default: 0,
                            placeholder: "会员等级最高价",
                            tips: "会员等级最高价，商品的价格不得高于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        },
                        {
                            title: "会员密价最低价",
                            name: "market_control_user_min_price",
                            type: "number",
                            default: 0,
                            placeholder: "会员密价最低价",
                            tips: "会员密价最低价，商品的价格不得低于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        },
                        {
                            title: "会员密价最高价",
                            name: "market_control_user_max_price",
                            type: "number",
                            default: 0,
                            placeholder: "会员密价最高价",
                            tips: "会员密价最高价，商品的价格不得高于该价格进行零售，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        },
                        {
                            title: "单次最低购买数",
                            name: "market_control_min_num",
                            type: "number",
                            placeholder: "单次下单最低购买数量",
                            default: 0,
                            tips: "单次下单最低购买数量，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        },
                        {
                            title: "单次最多购买数",
                            name: "market_control_max_num",
                            type: "number",
                            placeholder: "单次下单最多购买数量",
                            default: 0,
                            tips: "单次下单最多购买数量，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        },
                        {
                            title: "每人最多购买数",
                            name: "market_control_only_num",
                            type: "number",
                            default: 0,
                            placeholder: "每个人最多购买同一个SKU数量，超过则无法再进行购买",
                            tips: "每个人最多购买同一个SKU数量，超过则无法再进行购买，如果为'0'代表不限制",
                            hide: assign.market_control_status != 1
                        }
                    ]
                }
            ],
            assign: assign,
            autoPosition: true,
            width: "460px",
            done: () => {
                $table && $table.refresh();
            }
        });
    }

    table = new Table("/admin/repertory/item/get", "#repertory-item-table");
    table.setColumns([
        {checkbox: true},
        {field: 'supplier', title: '供货商', class: "nowrap", formatter: format.user},
        {field: 'name', title: '货源名称'},
        {
            field: 'sku', title: 'SKU/出库价/成本/库存', formatter: (sku, item) => {
                let html = "";
                sku.forEach(g => {
                    html += format.badge(`${g.name} / <span class="text-warning">${getVar("CCY")}${format.amountRemoveTrailingZeros(g.stock_price)}</span> / ${format.amountRemoveTrailingZeros(item.user_id > 0 ? g.supply_price : g.cost)} / <span class="text-success">${g.stock}</span>`, "acg-badge-h-dodgerblue nowrap");
                });
                return html;
            }
        },
        {field: 'plugin_name', title: '插件'},
        {field: 'status', title: '状态', class: "nowrap", dict: "repertory_item_status"},
        {field: 'sort', title: '排序', type: 'input', reload: true, width: 85, sort: true},
        {
            field: 'operation', class: "nowrap", title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-shenhe',
                    class: 'acg-badge-h-tan',
                    click: (event, value, row, index) => {
                        message.ask("请在提交审核前确认该商品的进货价格已进行相应调整", () => {
                            util.post("/admin/repertory/item/save", {id: row.id, status: 1}, res => {
                                table.refresh();
                            });
                        });
                    },
                    show: item => {
                        return item.status == 0;
                    }
                },
                {
                    icon: 'icon-fuzhi',
                    class: 'acg-badge-h-tan',
                    click: (event, value, row, index) => {
                        delete row.id;
                        modal(`${util.icon("icon-tianjia")} 添加货源`, row);
                    },
                    tips: "复制货源"
                },
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-a-xiugai2") + "<space></space>" + row.name.replace(/(<([^>]+)>)/ig, "").substring(0, 4) + "..", row);
                    },
                    tips: "修改货源"
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/repertory/item/del", [row.id], () => {
                            table.refresh();
                        });
                    },
                    tips: "删除货源"
                }
            ]
        },
    ]);
    table.setPagination(10, [10, 30, 50, 100, 200]);
    table.setFloatMessage([
        {field: 'api_code', title: '对接码'},
        {field: 'privacy', title: '对接权限', dict: "repertory_item_privacy"},
        {field: 'user_item_count', title: '被接入次数'},
        {
            field: 'today_amount', class: "nowrap", title: '今日出库额', sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'yesterday_amount', class: "nowrap", title: '昨日出库额', sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'weekday_amount', title: '本周出库额', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'month_amount', title: '本月出库额', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'last_month_amount', title: '上月出库额', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'order_amount', title: '总交易出库额', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {field: 'create_time', title: '创建时间'}
    ]);

    table.setSearch([
        {
            title: "显示范围：整站", name: "display_scope", type: "select", dict: [
                {id: 1, name: "仅主站"},
                {id: 2, name: "仅供货商"}
            ], change: (search, val) => {
                if (val == 2) {
                    search.show("user_id");
                    search.hide("equal-plugin");
                } else if (val == 1) {
                    search.selectClearOption("equal-plugin");
                    search.selectReload("equal-plugin", "ship");
                    search.show("equal-plugin");
                } else {
                    search.hide("user_id");
                    search.hide("equal-plugin");
                }
            }
        },
        {
            title: "搜索供货商",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=1",
            hide: true,
            change: (search, val) => {
                search.selectClearOption("equal-plugin");
                search.selectReload("equal-plugin", `ship?userId=${val}`);
                search.show("equal-plugin");
            }
        },
        {title: "货源插件", name: "equal-plugin", type: "select", dict: "ship", hide: true},
        {title: "货源关键词", name: "search-name", type: "input"},
        {title: "分类", name: "equal-repertory_category_id", type: "select", dict: "repertoryCategory"},
        {title: "对接权限", name: "equal-privacy", type: "select", dict: "repertory_item_privacy"}
    ]);
    table.setState("status", "repertory_item_status");
    table.setUpdate("/admin/repertory/item/save");
    table.setDeleteSelector(".del-repertory-item", "/admin/repertory/item/del");
    table.onResponse(data => {
        $('.data-count .shelves-have-count').html(data.data.shelves_have_count);
        $('.data-count .under-review-count').html(data.data.under_review_count);
        $('.data-count .shelves-not-count').html(data.data.shelves_not_count);
        $('.data-count .banned-count').html(data.data.banned_count);
        $('.data-count .item_count').html(data.data.shelves_have_count + data.data.under_review_count + data.data.shelves_not_count + data.data.banned_count);
    });
    table.render();

    $('.add-repertory-item').click(() => {
        modal(`${util.icon("icon-tianjia")} 添加货源`);
    });

    $('.transfer-repertory-item').click(() => {
        let data = table.getSelectionIds();
        if (data.length == 0) {
            layer.msg(i18n("请勾选要操作的货源 (·•᷄ࡇ•᷅ ）"));
            return;
        }

        component.popup({
            submit: (res, index) => {
                res.data = data;
                util.post("/admin/repertory/item/transferShop", res, ret => {
                    table.refresh();
                    message.alert("导入成功，如果你还要导入更多商品，可以继续操作。");
                    layer.close(index);
                })
            },
            confirmText: util.icon("icon-daochu2") + "立即导入",
            tab: [
                {
                    name: util.icon("icon-shangxiajia") + " 选择你要入库的分类",
                    form: [
                        {
                            title: "商品分类",
                            name: "category_id",
                            type: "treeSelect",
                            placeholder: "请选择直营店的商品分类",
                            dict: "shopCategory",
                            search: true,
                            required: true,
                            parent: false
                        },
                        {
                            title: "同步模版",
                            name: "markup_id",
                            type: "select",
                            placeholder: "请选择模版",
                            dict: "itemMarkupTemplate",
                            required: true
                        }
                    ]
                }
            ],
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            autoPosition: true,
            height: "auto",
            width: "580px",
            maxmin: false
        });

    });

    $('.item-up').click(() => {
        let selections = table.getSelections();
        let data = table.getSelectionIds();

        if (data.length == 0) {
            layer.msg(i18n("请勾选要操作的货源 (·•᷄ࡇ•᷅ ）"));
            return;
        }

        util.post("/admin/repertory/item/updateStatus", {list: data, status: 1}, res => {
            table.refresh();
            selections.forEach(item => {
                message.success(`「${item.name}」已上架`);
            });
        });
    });

    $('.item-down').click(() => {
        let selections = table.getSelections();
        let data = table.getSelectionIds();
        if (data.length == 0) {
            layer.msg(i18n("请勾选要操作的货源 (·•᷄ࡇ•᷅ ）"));
            return;
        }
        util.post("/admin/repertory/item/updateStatus", {list: data, status: 0}, res => {
            table.refresh();
            selections.forEach(item => {
                message.success(`「${item.name}」已下架`);
            });
        });
    });

    $('.item-local').click(() => {
        let selections = table.getSelections();
        let data = table.getSelectionIds();
        if (data.length == 0) {
            layer.msg(i18n("请勾选要本地化的货源 (·•᷄ࡇ•᷅ ）"));
            return;
        }

        component.popup({
            submit: (res, index) => {
                res.local = 1;
                res.markup_mode = 0;

                message.success(`本地化开始执行..`);
                const loaderIndex = layer.load(2, {shade: ['0.3', '#fff']});
                let itemIndex = 0;

                util.timer(() => {
                    return new Promise(resolve => {
                        const item = selections[itemIndex];
                        itemIndex++;

                        if (!item) {
                            layer.close(loaderIndex);
                            layer.close(index);
                            message.success(`本地化执行完毕!`);
                            table.refresh();
                            resolve(false);
                        }

                        if (item.user_id > 0) {
                            message.warning(`「${item.name}」此货源属于供货商，已跳过..`);
                            resolve(true);
                            return;
                        }

                        if (!item.unique_id) {
                            message.warning(`「${item.name}」此货源已是本地货源，已跳过..`);
                            resolve(true);
                            return;
                        }

                        res.id = item.id;

                        util.post({
                            url: "/admin/repertory/item/save",
                            loader: false,
                            data: res,
                            error: () => resolve(true),
                            fail: () => resolve(true),
                            done: () => {
                                message.success(`「${item.name}」本地化成功!`);
                                resolve(true);
                            }
                        });
                    });
                }, 1, true);
            },
            confirmText: util.icon("icon-xiazai") + " 立即本地化",
            tab: [
                {
                    name: util.icon("icon-xiazai") + " 请选择本地发货插件",
                    form: [
                        {
                            title: false,
                            name: "custom_tips",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(`
<div class="block-tips">
<p>请谨慎使用此功能。使用后，所有被选中的远程商品将被本地化处理，且上游对接信息将被完全删除，系统将由本地货源插件全权接管商品管理。</p>
</div>
                                    `);

                            }
                        },
                        {
                            title: "发货插件",
                            name: "plugin",
                            type: "radio",
                            placeholder: "请选择",
                            dict: "ship",
                            required: true,
                            change: (popup, val) => {
                                shipForm.forEach(form => {
                                    popup.removeForm(form.name);
                                });
                                shipName = val;
                                if (val == "" || val == null) {
                                    return;
                                }
                                util.post({
                                    url: "/admin/plugin/submit/js?name=" + val + "&js=Item.Form",
                                    done: res => {
                                        if (!res?.data?.code) {
                                            return;
                                        }
                                        let data = eval('(' + res.data.code + ')');
                                        if (data == "") {
                                            return;
                                        }
                                        shipForm = data;
                                        data.forEach(form => {
                                            popup.createForm(form, "plugin", "after");
                                        });
                                    }
                                });
                            },
                        }
                    ]
                }
            ],
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            autoPosition: true,
            width: "480px",
            maxmin: false
        });
    });
}();