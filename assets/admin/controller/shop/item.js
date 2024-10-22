!function () {
    let table, skuTable, skuGroupTable, tempId, skuTempId, skuUserTable, skuWholesaleTable;
    const modal = (title, assign = {}) => {
        tempId = util.generateRandStr(16);
        let tabs = [
            {
                name: title,
                form: [
                    {
                        title: "商品分类",
                        name: "category_id",
                        type: "treeSelect",
                        placeholder: "请选择商品分类",
                        dict: 'shopCategory?userId=' + assign?.user_id,
                        required: true,
                        parent: false
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
                        title: "商品名称",
                        name: "name",
                        type: "textarea",
                        height: 34,
                        placeholder: "请输入商品名称，支持自定义HTML美化",
                        picker: true,
                        required: true
                    },
                    {
                        title: "上架",
                        name: "status",
                        type: "switch",
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
                        title: "推荐",
                        name: "recommend",
                        type: "switch",
                        tips: "推荐的商品，会在主页显示"
                    }
                ]
            },
            {
                name: util.icon("icon-shuoming") + " 商品介绍",
                form: [
                    {
                        name: "introduce",
                        type: "editor",
                        placeholder: "介绍一下你的商品信息吧",
                        height: 660,
                        uploadUrl: "/admin/upload",
                        photoAlbumUrl: '/admin/upload/get',
                    },
                ]
            },
            {
                name: util.icon("icon-tubiaoguifan-09") + " SKU",
                form: [
                    {
                        name: "sku",
                        type: "custom",
                        complete: (popup, dom) => {
                            dom.html(`<div class="block block-rounded"><div class="block-content"><table id="shop-itemSku-table"></table></div></div>`);
                            skuTable = new Table("/admin/shop/item/sku/get?id=" + assign.id, dom.find('#shop-itemSku-table'));
                            skuTable.setUpdate("/admin/shop/item/sku/save");
                            skuTable.setColumns([
                                {field: 'sort', title: '排序', type: 'input', reload: true, width: 65},
                                {field: 'name', title: 'SKU名称', class: "nowrap", formatter: format.item},
                                {
                                    field: 'price',
                                    title: '零售价',
                                    type: 'input',
                                    reload: true,
                                    width: 95,
                                    formatter: format.amountRemoveTrailingZeros
                                },

                                {
                                    field: 'sku_entity.stockPrice',
                                    class: "nowrap",
                                    title: '进货价',
                                    formatter: format.amountRemoveTrailingZeros
                                },
                                {
                                    field: 'dividend_amount', class: "nowrap", title: '分红', formatter: amount => {
                                        if (amount > 0) {
                                            return format.amountRemoveTrailingZeros(amount);
                                        }
                                        return '-';
                                    }
                                },
                                {
                                    field: 'sku_entity',
                                    class: "nowrap",
                                    title: '预计盈利',
                                    formatter: (entity, sku) => {
                                        return format.amountRemoveTrailingZeros((new Decimal(sku.price, 6)).sub(entity.stockPrice).sub(sku.dividend_amount).getAmount(6));
                                    }
                                },
                                {
                                    field: 'sku_entity',
                                    class: "nowrap",
                                    title: '控价(范围)',
                                    formatter: (entity, sku) => {
                                        if (!entity.marketControl) {
                                            return '-';
                                        }
                                        return (entity.marketControlMinPrice == 0 ? i18n('无限制') : format.amountRemoveTrailingZeros(entity.marketControlMinPrice)) + " ~ " + (entity.marketControlMaxPrice == 0 ? i18n('无限制') : format.amountRemoveTrailingZeros(entity.marketControlMaxPrice));
                                    }
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
                                    field: 'wholesale',
                                    title: '批发',
                                    width: 95,
                                    class: "nowrap",
                                    type: 'button',
                                    buttons: [
                                        {
                                            icon: 'icon-shezhi',
                                            title: '配置',
                                            class: 'acg-badge-h-setting',
                                            click: (event, value, row, index) => {
                                                wholesaleModal(util.icon("icon-jiajushebeipiliangbanqianshenqingbiao") + " 批发设置", row.id, row.user_id);
                                            }
                                        }
                                    ]
                                },
                                {
                                    field: 'operation',
                                    title: '操作',
                                    width: 95,
                                    class: "nowrap",
                                    type: 'button',
                                    buttons: [
                                        {
                                            icon: 'icon-biaoge-xiugai',
                                            title: '修改',
                                            class: 'acg-badge-h-dodgerblue',
                                            click: (event, value, row, index) => {
                                                skuModal(util.icon("icon-biaoge-xiugai") + " 修改SKU", row, {popup: popup});
                                            }
                                        }
                                    ]
                                },
                            ]);
                            skuTable.render();
                        }
                    },
                ]
            },
            {
                name: util.icon("icon-jiage") + " 盈利/同步配置",
                form: [
                    {
                        title: "配置模式",
                        name: "markup_mode",
                        type: "radio",
                        tips: "独立配置：每个商品独立配置<br>模板配置：选择一个创建好的模板，由模板统一管理价格盈亏",
                        dict: "markup_mode",
                        change: (obj, value) => {
                            if (value == 0) {
                                obj.hide("markup_template_id");
                                //obj.show("markup.drift_base_amount");
                                //obj.show("markup.drift_model");
                                //obj.show("markup.drift_value");
                                obj.show("markup.sync_name");
                                obj.show("markup.sync_introduce");
                                obj.show("markup.sync_picture");
                                obj.show("markup.sync_sku_name");
                                obj.show("markup.sync_sku_picture");
                                obj.show("markup.sync_amount");
                                obj.show("price_module");
                                obj.show("info_module");
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
                        tips: "如果这里没有模板，请先到加价模板中进行新增",
                        placeholder: "请选择模板",
                        dict: "itemMarkupTemplate?userId=" + assign.user_id
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
                            {id: 1, name: "同步仓库并加价"},
                            {id: 2, name: "同步仓库"}
                        ],
                        required: true,
                        tips: "不同步：完全由本地自定义价格\n同步仓库并加价：根据仓库的商品价格实时控制盈亏\n同步仓库：仓库是什么价格，本地商品就是什么价格".replaceAll("\n", "<br>"),
                        change: (from, val) => {
                            val = parseInt(val);
                            switch (val) {
                                case 0:
                                    from.hide('markup.keep_decimals');
                                    from.hide('markup.drift_base_amount');
                                    from.hide('markup.drift_model');
                                    from.hide('markup.drift_value');
                                    break;
                                case 1:
                                    from.show('markup.keep_decimals');
                                    from.show('markup.drift_base_amount');
                                    from.show('markup.drift_model');
                                    from.show('markup.drift_value');
                                    break;
                                case 2:
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
                        tips: "基数就是你随便设定一个商品的成本价，比如你想象一个商品的成本价是10元，那么你就把基数设定为10元。<br><br>为什么要有这个设定呢？因为每个商品都有不同的类型和价格，设定一个基数可以帮助我们计算出你想给某个商品增加的价格。通过基数，我们可以简单地推算出商品的最终价格。",
                        placeholder: "请设定基数",
                        hide: true,
                        default: 10,
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
                        hide: true,
                        tips: "百分比 或 金额，根据加价模式自行填写，百分比需要用小数表示",
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
                ]
            }
        ];

        component.popup({
            submit: '/admin/shop/item/save',
            tab: tabs,
            assign: assign,
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            width: "1280px",
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
                        title: "SKU封面",
                        name: "picture_url",
                        type: "image",
                        placeholder: "请选择封面图片",
                        uploadUrl: '/admin/upload?thumb_height=128',
                        photoAlbumUrl: '/admin/upload/get',
                        height: 300,
                        change: (form, url, data) => {
                            form.setInput("picture_thumb_url", data.append.thumb_url);
                            message.success("缩略图已生成");
                        }
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
                        picker: true
                    },
                    {
                        title: "零售价",
                        name: "price",
                        type: "number",
                        placeholder: "进货价",
                        tips: "【零售价】该价格决定了客户会支付多少钱购买该商品"
                    },
                    {
                        title: "分红金额",
                        name: "dividend_amount",
                        type: "number",
                        placeholder: "分红金额",
                        tips: "【分红金额】如果会员推广了此商品，那么他将会获得多少分红，不填写或0代表该会员一分钱也分不到"
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
                        tips: "启用私密模式后，只有设置过独立显示的【会员等级】或【会员】才可以看到该SKU，如该商品没有任何SKU可以购买，商品则会完全隐藏。"
                    }
                ]
            },
            {
                name: util.icon("icon-dengji") + " 会员等级",
                form: [
                    {
                        name: "group",
                        type: "custom",
                        complete: (popup, dom) => {
                            console.log(assign);
                            dom.html(`<div class="block block-rounded"> <div class="block-content mt-0 pt-0"><table id="shop-item-sku-group-table"></table> </div> </div>`);
                            skuGroupTable = new Table("/admin/shop/item/sku/level/get?id=" + assign.id + "&userId=" + assign.user_id, dom.find('#shop-item-sku-group-table'));
                            skuGroupTable.setUpdate("/admin/shop/item/sku/level/save?skuId=" + assign.id + "&userId=" + assign.user_id);
                            skuGroupTable.setColumns([
                                {
                                    field: 'name', title: '等级', class: 'nowrap', formatter: (name, item) => {
                                        return format.group(item);
                                    },
                                },
                                {
                                    field: 'item_sku_level.price',
                                    title: '零售价',
                                    type: 'input',
                                    formatter: format.amountRemoveTrailingZeros,
                                    width: 100
                                },
                                {
                                    field: 'item_sku_level.dividend_amount',
                                    title: '分红金额',
                                    type: 'input',
                                    formatter: format.amountRemoveTrailingZeros,
                                    width: 100
                                },
                                {
                                    field: 'item_sku_level.status',
                                    title: '状态',
                                    type: 'switch',
                                    text: "启用|关闭",
                                    width: 100
                                }
                            ]);
                            skuGroupTable.render();
                            //-------------
                        }
                    }
                ]
            }, {
                name: util.icon("icon-kehudengjiicon") + " 会员",
                form: [
                    {
                        name: "user",
                        type: "custom",
                        complete: (popup, dom) => {
                            dom.html(`<div class="block block-rounded"><div class="block-content mt-0 pt-0"><table id="shop-item-sku-user-table"></table></div></div>`);

                            skuUserTable = new Table("/admin/shop/item/sku/user/get?id=" + assign.id + "&userId=" + assign.user_id, dom.find('#shop-item-sku-user-table'));
                            skuUserTable.setUpdate("/admin/shop/item/sku/user/save?skuId=" + assign.id + "&userId=" + assign.user_id);
                            skuUserTable.setColumns([
                                {
                                    field: 'username', title: '会员', class: 'nowrap', formatter: function (val, item) {
                                        return format.client(item);
                                    }
                                },
                                {
                                    field: 'item_sku_user.price',
                                    title: '零售价',
                                    type: 'input',
                                    width: 100,
                                    formatter: format.amountRemoveTrailingZeros
                                },
                                {
                                    field: 'item_sku_user.dividend_amount',
                                    title: '分红金额',
                                    type: 'input',
                                    formatter: format.amountRemoveTrailingZeros,
                                    width: 100
                                },
                                {
                                    field: 'item_sku_user.status',
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


        component.popup({
            submit: '/admin/shop/item/sku/save',
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
            width: "580px",
            done: () => {
                skuTable.refresh();
            }
        });


    }
    const wholesaleModal = (title, skuId, userId) => {
        component.popup({
            tab: [
                {
                    name: title,
                    form: [
                        {
                            name: "wholesale",
                            type: "custom",
                            complete: (popup, dom) => {
                                dom.html(`<div class="block block-rounded"><div class="block-content"><table id="shop-item-sku-wholesale-table"></table></div></div>`);

                                skuWholesaleTable = new Table("/admin/shop/item/sku/wholesale/get?id=" + skuId, dom.find('#shop-item-sku-wholesale-table'));
                                skuWholesaleTable.setUpdate("/admin/shop/item/sku/wholesale/save");
                                skuWholesaleTable.setDeleteSelector(".del-shop-item-sku-wholesale", "/admin/shop/item/sku/wholesale/del");
                                skuWholesaleTable.disablePagination();
                                skuWholesaleTable.setColumns([
                                    {field: 'quantity', title: '数量', class: 'nowrap'},
                                    {
                                        field: 'price',
                                        title: '批发价',
                                        type: 'input',
                                        width: 95,
                                        formatter: format.amountRemoveTrailingZeros
                                    },
                                    {
                                        field: 'dividend_amount',
                                        title: '分红金额',
                                        type: 'input',
                                        formatter: format.amountRemoveTrailingZeros,
                                        width: 95
                                    },
                                    {
                                        field: 'realtime_stock_price',
                                        title: '进货价',
                                        formatter: format.amountRemoveTrailingZeros
                                    },
                                    {
                                        field: 'level', title: '会员等级', class: 'nowrap', type: 'button', buttons: [
                                            {
                                                icon: "icon-shezhi",
                                                title: '配置',
                                                class: 'acg-badge-h-setting',
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
                                                                            dom.html(`<div class="block block-rounded"><div class="block-content mt-0 pt-0"><table id="shop-item-sku-wholesale-group-table"></table></div></div>`);

                                                                            const levelTable = new Table("/admin/shop/item/sku/wholesale/level/get?id=" + row.id + "&userId=" + userId, dom.find('#shop-item-sku-wholesale-group-table'));
                                                                            levelTable.setUpdate("/admin/shop/item/sku/wholesale/level/save?id=" + row.id + "&userId=" + userId);
                                                                            levelTable.setColumns([
                                                                                {
                                                                                    field: 'name',
                                                                                    title: '会员等级',
                                                                                    formatter: (val, item) => {
                                                                                        return format.group(item);
                                                                                    },
                                                                                    class: "nowrap",
                                                                                },
                                                                                {
                                                                                    field: 'item_sku_wholesale_level.price',
                                                                                    title: '批发价格',
                                                                                    type: 'input',
                                                                                    formatter: format.amountRemoveTrailingZeros,
                                                                                    width: 100
                                                                                },
                                                                                {
                                                                                    field: 'item_sku_wholesale_level.dividend_amount',
                                                                                    title: '分红金额',
                                                                                    type: 'input',
                                                                                    formatter: format.amountRemoveTrailingZeros,
                                                                                    width: 100
                                                                                },
                                                                                {
                                                                                    field: 'item_sku_wholesale_level.status',
                                                                                    title: '状态',
                                                                                    type: 'switch',
                                                                                    text: "启用|关闭",
                                                                                    width: 100
                                                                                }
                                                                            ]);
                                                                            levelTable.render();
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
                                    {
                                        field: 'user', title: '会员', type: 'button', class: 'nowrap', buttons: [
                                            {
                                                icon: "icon-yonghu",
                                                title: "配置",
                                                class: 'acg-badge-h-setting',
                                                click: (event, value, row, index) => {
                                                    //------------------------------
                                                    component.popup({
                                                        tab: [
                                                            {
                                                                name: util.icon("icon-yonghu") + " 批发设置 -> [数量:" + row.quantity + "] -> 会员",
                                                                form: [
                                                                    {
                                                                        name: "wholesale_user",
                                                                        type: "custom",
                                                                        complete: (popup, dom) => {
                                                                            dom.html(`<div class="block block-rounded"><div class="block-content mt-0 pt-0"><table id="shop-item-sku-wholesale-user-table"></table></div></div>`);

                                                                            const userTable = new Table("/admin/shop/item/sku/wholesale/user/get?id=" + row.id + "&userId=" + userId, dom.find('#shop-item-sku-wholesale-user-table'));
                                                                            userTable.setUpdate("/admin/shop/item/sku/wholesale/user/save?id=" + row.id + "&userId=" + userId);
                                                                            userTable.setColumns([
                                                                                {
                                                                                    field: 'username',
                                                                                    title: '会员',
                                                                                    formatter: function (val, item) {
                                                                                        return format.client(item);
                                                                                    },
                                                                                    class: "nowrap",
                                                                                },
                                                                                {
                                                                                    field: 'item_sku_wholesale_user.price',
                                                                                    title: '批发价格',
                                                                                    type: 'input',
                                                                                    width: 100,
                                                                                    formatter: format.amountRemoveTrailingZeros
                                                                                },
                                                                                {
                                                                                    field: 'item_sku_wholesale_user.dividend_amount',
                                                                                    title: '分红金额',
                                                                                    type: 'input',
                                                                                    formatter: format.amountRemoveTrailingZeros,
                                                                                    width: 100
                                                                                },
                                                                                {
                                                                                    field: 'item_sku_wholesale_user.status',
                                                                                    title: '状态',
                                                                                    type: 'switch',
                                                                                    text: "启用|关闭",
                                                                                    width: 100,
                                                                                }
                                                                            ]);

                                                                            userTable.setSearch([
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
                                                                            userTable.render();
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
                                //-------------
                            }
                        }
                    ]
                }
            ],
            autoPosition: true,
            width: "620px"
        });
    }

    table = new Table("/admin/shop/item/get", "#shop-item-table");
    table.setDeleteSelector(".del-shop-item", "/admin/shop/item/del");
    table.setUpdate("/admin/shop/item/save");
    table.setColumns([
        {checkbox: true},
        /*  {field: 'picture_url', title: '#', render: "image", style: "border-radius:25%;"},*/
        {field: 'user', title: '商家', class: "nowrap", formatter: format.user},
        {field: 'category.name', title: '分类'},
        {field: 'name', title: '商品名称'},
        {
            field: 'sku', title: 'SKU/单价/库存', formatter: (sku, item) => {
                let html = "";
                sku.forEach(g => {
                    html += format.badge(`${g.name} / ${getVar("CCY")}${format.amountRemoveTrailingZeros(g.price)} / ${g.stock}`, "acg-badge-h-dodgerblue nowrap");
                });
                return html;
            }
        },
        {
            field: 'repertory_item', title: '货源', class: "nowrap", align: "center", formatter: item => {
                if (item?.status == 2) {
                    return format.success("正常");
                }
                return format.danger("维护中");
            }
        },
        {field: 'recommend', title: '推荐', type: "switch", class: "nowrap", text: "ON|OFF", width: 85},
        {
            field: 'status',
            title: '状态',
            type: "switch",
            class: "nowrap",
            text: "在售|已下架",
            reload: true,
            width: 100
        },
        {field: 'sort', title: '排序', type: 'input', reload: true, width: 70},
        {
            field: 'operation', title: '操作', class: "nowrap", type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        modal("<i class='fa fa-edit'></i> " + row.name.replace(/(<([^>]+)>)/ig, "").substring(0, 4) + "..", row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/admin/shop/item/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);
    table.setFloatMessage([
        {
            field: 'today_amount', title: '今日交易', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'yesterday_amount', title: '昨日交易', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'weekday_amount', title: '本周交易', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'month_amount', title: '本月交易', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'last_month_amount', title: '上月交易', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        },
        {
            field: 'order_amount', title: '总交易交易', class: "nowrap", sort: true, formatter: amount => {
                if (amount <= 0) {
                    return "-";
                }
                return format.money(amount, "#9089ce", false);
            }
        }
    ])
    table.onResponse(data => {
        $('.data-count .item_count').html(data.data.item_count);
        $('.data-count .sold_count').html(data.data.sold_count);
        $('.data-count .not_sold_count').html(data.data.not_sold_count);
    });
    table.setSearch([
        {
            title: "商家，默认主站",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=2",
            change: (search, value, selected) => {
                if (selected) {
                    search.treeSelectReload("equal-category_id", "shopCategory?userId=" + value);
                } else {
                    search.treeSelectReload("equal-category_id", "shopCategory");
                }
            }
        },
        {title: "商品分类", name: "equal-category_id", type: "treeSelect", dict: "shopCategory"},
        {title: "查找商品关键词", name: "search-name", type: "input"}
    ]);
    table.setState('status', 'shop_item_status');
    table.render();


    $('.control-item').click(() => {
        const selections = table.getSelections();
        if (selections.length == 0) {
            layer.msg("至少选中1个商品才可以进行操作");
            return;
        }
        component.popup({
            submit: data => {
                let index = 0;
                const startLoadIndex = layer.load(2, {shade: ['0.3', '#fff']});
                util.timer(() => {
                    return new Promise(resolve => {
                        const row = selections[index];
                        index++;
                        if (row) {
                            data.id = row.id;
                            util.post({
                                url: "/admin/shop/item/save",
                                data: data,
                                loader: false,
                                done: (response, index) => {
                                    message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${row?.name}] 已操作成功!`);
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
                }, 300, true);
            },
            tab: [
                {
                    name: util.icon("icon-shangxiajia") + " 批量更改商品",
                    form: [
                        {
                            title: "上架",
                            name: "status",
                            type: "switch",
                        },
                        {
                            title: "推荐",
                            name: "recommend",
                            type: "switch",
                            tips: "推荐的商品，会在主页显示"
                        }
                    ]
                }
            ],
            assign: {},
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            height: "auto",
            width: "380px",
            closeBtn: false,
            done: () => {
                table.refresh();
            }
        });
    });
}();