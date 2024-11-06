!function () {
    let table, skuTable, skuGroupTable, tempId, skuTempId, skuUserTable, skuWholesaleTable, shipForm = [], shipName;

    const modal = (title, assign = {}) => {
        tempId = util.generateRandStr(16);

        let tabs = [
            {
                name: title,
                form: [
                    {
                        title: "user_id",
                        name: "user_id",
                        type: "input",
                        placeholder: "user_id",
                        hide: true,
                        default: 0
                    },
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
                        default: getVar("isMerchant") != 1 ? 0 : 1,
                        tips: "开启此选项后，商品将直接入库至直营店，并在网站首页以可购买状态展示",
                        change: (form, val) => {
                            if (val == 1) {
                                form.show('direct_category_id');
                            } else {
                                form.hide('direct_category_id');
                            }
                        },
                        hide: assign?.id > 0 || getVar("isMerchant") != 1
                    },
                    {
                        title: "直营店分类",
                        name: "direct_category_id",
                        type: "treeSelect",
                        placeholder: "请选择直营店的商品分类",
                        dict: getVar("isMerchant") == 1 ? 'shopCategory' : 'repertoryCategory',
                        parent: false,
                        hide: assign?.id > 0 || getVar("isMerchant") != 1
                    },
                    {
                        title: "仓库分类",
                        name: "repertory_category_id",
                        type: "treeSelect",
                        placeholder: "请选择仓库分类",
                        dict: 'repertoryCategory',
                        regex: {
                            value: "^[1-9]\\d*$",
                            message: "必须选中一个分类"
                        },
                        required: true,
                        parent: false
                    },
                    {
                        title: "商品名称",
                        name: "name",
                        type: "textarea",
                        height: 34,
                        placeholder: "请输入商品名称，支持自定义HTML美化",
                        required: true
                    },
                    {
                        title: "商品封面",
                        name: "picture_url",
                        type: "image",
                        placeholder: "请选择封面图片",
                        uploadUrl: '/user/upload?thumb_height=128',
                        photoAlbumUrl: '/user/upload/get',
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
                        title: "对接权限",
                        name: "privacy",
                        type: "select",
                        placeholder: "请选择对接权限",
                        default: 0,
                        dict: "repertory_item_privacy",
                        required: true
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
                        3.无理由退款：根据商品设置的资金冻结期限，所有与订单相关的资金将被冻结，只有等到解冻时间后，才可以使用这部分资金。`.trim().replaceAll("\n", "<br><br>"),
                        required: true
                    },
                    {
                        title: "自动收货时效",
                        name: "auto_receipt_time",
                        type: "input",
                        placeholder: "自动收货时效",
                        default: 5040,
                        tips: "自动收货时效，单位/分钟，如果为'0'的情况下，货物会发货并且立即收货，不需要经过顾客同意",
                        required: true
                    },
                ]
            },
            {
                name: util.icon("icon-shuoming") + "<space></space>商品介绍",
                form: [
                    {
                        name: "introduce",
                        uploadUrl: "/user/upload",
                        photoAlbumUrl: '/user/upload/get',
                        type: "editor",
                        placeholder: "介绍一下你的商品信息吧",
                        height: 660
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
                                url: "/user/plugin/submit/js?name=" + val + "&js=Item.Form",
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
                        },
                        required: true
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
            <button type="button" class="btn btn-outline-success btn-sm add-repertory-itemSku">` + util.icon("icon-tianjia") + `<space></space>` + i18n("添加SKU") + `
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm del-repertory-itemSku">` + util.icon("icon-shanchu") + `<space></space>` + i18n("移除SKU") + `
            </button>
        </div>
        <div class="block-content">
            <table id="repertory-itemSku-table"></table>
        </div>
    </div>`);

                            skuTable = new Table("/user/repertory/item/sku/get?id=" + (assign.id ?? tempId) + "&type=" + (assign.id ? "edit" : "add"), dom.find('#repertory-itemSku-table'));
                            skuTable.setDeleteSelector(".del-repertory-itemSku", "/user/repertory/item/sku/del");
                            skuTable.setUpdate("/user/repertory/item/sku/save");
                            skuTable.setColumns([
                                {checkbox: true},
                                {field: 'name', title: 'SKU名称', class: "nowrap", formatter: format.item},
                                {
                                    field: 'supply_price',
                                    title: '供货价',
                                    type: 'input',
                                    reload: true,
                                    width: 95,
                                    formatter: format.amountRemoveTrailingZeros
                                },
                                {
                                    field: 'cost',
                                    title: '成本',
                                    type: 'input',
                                    reload: true,
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
                                                marketControlModal("/user/repertory/item/sku/save", row, skuTable);
                                            }
                                        }
                                    ]
                                },
                                {field: 'sort', title: '排序', type: 'input', reload: true, width: 75},
                                {
                                    field: 'operation',
                                    title: '操作',
                                    class: "nowrap",
                                    type: 'button',
                                    width: 110,
                                    buttons: [
                                        {
                                            icon: 'icon-biaoge-xiugai',
                                            title: '修改',
                                            class: 'acg-badge-h-dodgerblue',
                                            click: (event, value, row, index) => {
                                                skuModal(util.icon("icon-biaoge-xiugai") + " 修改SKU", row, {popup: popup});
                                            }
                                        },
                                        {
                                            icon: 'icon-shanchu1',
                                            class: 'acg-badge-h-red',
                                            title: "刪除",
                                            click: (event, value, row, index) => {
                                                component.deleteDatabase("/user/repertory/item/sku/del", [row.id], () => {
                                                    skuTable.refresh();
                                                });
                                            }
                                        }
                                    ]
                                },
                            ]);
                            skuTable.render();
                            //-------------


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
                                //    obj.show("markup.drift_base_amount");
                                //     obj.show("markup.drift_model");
                                //   obj.show("markup.drift_value");
                                obj.show("markup.sync_name");
                                obj.show("markup.sync_introduce");
                                obj.show("markup.sync_picture");
                                obj.show("markup.sync_sku_name");
                                obj.show("markup.sync_sku_picture");
                                obj.show("markup.sync_amount");
                                obj.show("price_module");
                                obj.show("info_module");
                                obj.show("markup.sync_remote_download");
                                obj.getData("markup.sync_amount") == 1 && obj.triggerOtherPopupChange("markup.sync_amount", 1);
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
                            {id: 0, name: "🚫不同步"},
                            {id: 1, name: "💲同步并自定义价格"},
                            {id: 2, name: "♻️同步上游"}
                        ],
                        required: true,
                        tips: "不同步：完全由本地自定义价格\n同步并自定义价格：根据上游的商品价格实时控制盈亏\n同步上游：上游是什么价格，本地商品就是什么价格".replaceAll("\n", "<br>"),
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
                                    [1, 3].includes(parseInt(from.getData("markup.drift_model"))) && from.show('markup.drift_base_amount');
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
                            assign?.markup_mode == 1 && obj.triggerOtherPopupChange("markup.sync_amount", value);
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
                        title: "加价模式",
                        name: "markup.drift_model",
                        type: "radio",
                        hide: true,
                        tips: format.success("比例向上/向下浮动") + " 如果你的商品是10元，那么【浮动值】设置 0.5，那么10元的商品最终售卖的价格就是：15【算法：10+(10*0.5)】<br>" + format.warning("固定金额向上/向下浮动") + " 通过基数+固定金额算法，得到的绝对比例进行加价，假如基数是10，加价1.2元，那么算法得出加价比例为：1.2÷10=0.12(12%)，假设一个商品为18元，最终售卖价格则是：20.16【算法：18+(18*0.12)】<br><br>注意：如果是向下浮动，就是把加法变成减法",
                        dict: "markup_type",
                        change: (form, val) => {
                            if (val == 1 || val == 3) {
                                form.show('markup.drift_base_amount');
                            } else {
                                form.hide('markup.drift_base_amount');
                            }
                        }
                    },
                    {
                        title: "价格基数",
                        name: "markup.drift_base_amount",
                        type: "input",
                        tips: "基数就是你随便设定一个商品的进货价，比如你想象一个商品的进货价是10元，那么你就把基数设定为10元。<br><br>为什么要有这个设定呢？因为每个商品都有不同的类型和价格，设定一个基数可以帮助我们计算出你想给某个商品增加的进货价。通过基数，我们可以简单地推算出商品的最终进货价。",
                        placeholder: "请设定基数",
                        default: 10,
                        required: true,
                        hide: assign?.markup?.sync_amount != 1 || assign?.markup?.drift_model == 0 || assign?.markup?.drift_model == 2,
                        regex: {
                            value: "^(0\\.\\d+|[1-9]\\d*(\\.\\d+)?)$", message: "基数必须大于0"
                        }
                    },
                    {
                        title: "浮动值",
                        name: "markup.drift_value",
                        type: "input",
                        hide: true,
                        tips: "【固定金额浮动模式】下填写具体金额<br><br>【比例浮动模式】下填写百分比，用小数代替，比如 10% 用小数表示就是 0.1，填写 0.1 即可",
                        placeholder: "请设置浮动值",
                        default: 0,
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

        if (!util.isObjectEmpty(getVar("hookItemPopup"))) {
            tabs = tabs.concat(getVar("hookItemPopup"));
        }

        component.popup({
            submit: '/user/repertory/item/save',
            tab: tabs,
            assign: assign,
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            width: "1000px",
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
                        uploadUrl: '/user/upload?thumb_height=128',
                        photoAlbumUrl: '/user/upload/get',
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
                        required: true
                    },
                    {
                        title: "供货价",
                        name: "supply_price",
                        type: "number",
                        placeholder: "供货价",
                        tips: "【供货价】就是平台给你结算的钱，另一个意思就是你准备赚多少钱？",
                        required: true
                    }, {
                        title: "成本",
                        name: "cost",
                        type: "number",
                        placeholder: "成本",
                        default: 0,
                        tips: "【成本】是用来为您计算盈利和盈亏的重要数据，请务必提供真实数据",
                        required: true
                    },
                    {
                        title: "排序",
                        name: "sort",
                        type: "number",
                        placeholder: "排序，越小越靠前",
                        default: 0,
                        tips: "数值越小，商品排名越靠前"
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
                        uploadUrl: "/user/upload",
                        photoAlbumUrl: '/user/upload/get',
                        height: 360
                    }
                ]
            },
        ];

        if (!shipName) {
            message.error("请选择发货插件，在添加/修改SKU");
            return;
        }

        util.post({
            url: "/user/plugin/submit/js?name=" + shipName + "&js=Sku.Tab",
            done: res => {
                let tab = [];
                if (res.data.code != "") {
                    tab = eval('(' + res.data.code + ')');
                }

                tabs = tabs.concat(tab);
                component.popup({
                    submit: '/user/repertory/item/sku/save',
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
                    width: "680px",
                    done: () => {
                        skuTable.refresh();
                    }
                });
            }
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

    table = new Table("/user/repertory/item/get", "#repertory-item-table");
    table.setDeleteSelector(".del-repertory-item", "/user/repertory/item/del");
    table.setUpdate("/user/repertory/item/save");
    table.setColumns([
        {checkbox: true},
        {field: 'name', title: '货物名称'},
        {
            field: 'sku', title: 'SKU/供货价/成本/库存', formatter: (sku, item) => {
                let html = "";
                sku.forEach(g => {
                    html += format.badge(`${g.name} / <span class="text-warning">${getVar("CCY")}${format.amountRemoveTrailingZeros(g.supply_price)}</span> / ${format.amountRemoveTrailingZeros(g.cost)} / <span class="text-success">${g.stock}</span>`, "acg-badge-h-dodgerblue nowrap");
                });
                return html;
            }
        },
        {field: 'plugin_name', title: '插件'},
        {field: 'status', title: '状态', dict: "repertory_item_status"},
        {
            field: 'is_direct_sale', title: '直营店', class: "nowrap", formatter: val => {
                if (val) {
                    return format.success('✅️');
                }

                return format.danger("🚫");
            }
        },
        {
            field: 'is_review', title: '供货状态', formatter: val => {
                if (val == 1) {
                    return format.danger("审核中");
                }
                return format.success("正常");
            }
        },
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
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
                        component.deleteDatabase("/user/repertory/item/del", [row.id], () => {
                            table.refresh();
                        });
                    },
                    tips: "删除货源"
                }
            ]
        },
    ]);
    table.setSearch([
        {title: "货源插件", name: "equal-plugin", type: "select", dict: "ship"},
        {title: "货物关键词", name: "search-name", type: "input"},
        {title: "分类", name: "equal-repertory_category_id", type: "treeSelect", dict: "repertoryCategory"},
        {title: "对接权限", name: "equal-privacy", type: "select", dict: "repertory_item_privacy"},
        {
            title: "供货状态", name: "equal-is_review", type: "select", dict: [
                {id: 0, name: "正常"},
                {id: 1, name: "审核中"}
            ]
        },
        {title: "直营店状态", name: "direct_status", type: "select", dict: "repertory_item_direct_status"},
    ]);
    table.setFloatMessage([
        {field: 'api_code', class: "nowrap", title: '对接码'},
        {field: 'privacy', title: '对接权限', dict: "repertory_item_privacy"},
        {field: 'user_item_count', class: "nowrap", title: '被接入次数'},
        {field: 'today_count', title: '今日出库'},
        {field: 'yesterday_count', title: '昨日出库'},
        {field: 'weekday_count', title: '本周出库'},
        {field: 'month_count', title: '本月出库'},
        {field: 'last_month_count', title: '上月出库'},
        {field: 'order_count', title: '总出库'},
        {title: '创建时间', field: 'create_time'}
    ]);

    table.setState("status", "repertory_item_status");
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

    $('.item-up').click(() => {
        let selections = table.getSelections();
        if (selections.length == 0) {
            layer.msg(i18n("请勾选要操作的货物 (·•᷄ࡇ•᷅ ）"));
            return;
        }

        let index = 0;
        const startLoadIndex = layer.load(2, {shade: ['0.3', '#fff']});
        util.timer(() => {
            return new Promise(resolve => {
                const row = selections[index];
                index++;
                if (row) {
                    util.post({
                        url: "/user/repertory/item/updateStatus",
                        data: {
                            id: row.id,
                            status: 1
                        },
                        loader: false,
                        done: (response, index) => {
                            message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${row?.name}] 已上架!`);
                            resolve(true);
                        },
                        error: (res) => {
                            message.error(`ヽ( ^ω^ ゞ ) [${row?.name}] ${res?.msg}`);
                            resolve(true);
                        },
                        fail: () => {
                            message.error(`ヽ( ^ω^ ゞ ) [${row?.name}] 网络错误!`);
                            resolve(true);
                        }
                    });
                    return;
                }
                table.refresh();
                layer.close(startLoadIndex);
                resolve(false);
            });
        }, 30, true);
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
                util.post("/user/shop/supply/dock", res, ret => {
                    table.refresh();
                    message.alert("接入完成，如果你还要接入更多商品，可以继续操作。");
                    layer.close(index);
                })
            },
            confirmText: util.icon("icon-daochu2") + "立即接入",
            tab: [
                {
                    name: util.icon("icon-shangxiajia") + " 选择直营店商品分类",
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
            width: "580px",
            maxmin: false
        });
    });

    $('.item-down').click(() => {
        let selections = table.getSelections();
        if (selections.length == 0) {
            layer.msg(i18n("请勾选要操作的货物 (·•᷄ࡇ•᷅ ）"));
            return;
        }

        let index = 0;
        const startLoadIndex = layer.load(2, {shade: ['0.3', '#fff']});
        util.timer(() => {
            return new Promise(resolve => {
                const row = selections[index];
                index++;
                if (row) {
                    util.post({
                        url: "/user/repertory/item/updateStatus",
                        data: {
                            id: row.id,
                            status: 0
                        },
                        loader: false,
                        done: (response, index) => {
                            message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${row?.name}] 已下架!`);
                            resolve(true);
                        },
                        error: (res) => {
                            message.error(`ヽ( ^ω^ ゞ ) [${row?.name}] ${res?.msg}`);
                            resolve(true);
                        },
                        fail: () => {
                            message.error(`ヽ( ^ω^ ゞ ) [${row?.name}] 网络错误!`);
                            resolve(true);
                        }
                    });
                    return;
                }
                table.refresh();
                layer.close(startLoadIndex);
                resolve(false);
            });
        }, 30, true);
    });
}();