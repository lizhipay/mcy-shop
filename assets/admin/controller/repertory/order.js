!function () {
    const table = new Table("/admin/repertory/order/get", "#repertory-order-table");
    table.setPagination(10, [10, 20, 30, 50, 100]);
    table.setColumns([
        {field: 'customer', title: '商家', formatter: format.user},
        {field: 'supplier', title: '供货商', formatter: format.user},
        {
            field: 'amount', title: '订单金额', formatter: function (amount) {
                if (amount == 0) {
                    return '-';
                }
                return format.money(amount, "#45bf77");
            }
        },
        {field: 'quantity', title: '数量'},
        {
            field: 'item.name', title: 'SKU/商品', formatter: (name, item) => {
                return `‹<small>${item?.sku?.name}</small>›` + name;
            }
        },

        {field: 'status', title: '状态', dict: "repertory_order_status"},

        {
            field: 'office_profit', title: '平台盈利', formatter: function (office_profit, item) {
                if (office_profit == 0 || ![0, 1, 2].includes(item.status)) {
                    return "-";
                }

                return format.money(office_profit, "#16d12c");
            }
        },
        {
            field: 'supply_profit', title: '成本', formatter: function (supply_profit, item) {
                const amount = item.supplier ? item.supply_profit : item.item_cost;
                if (amount <= 0) {
                    return '-';
                }
                return format.money(amount, "#fd5050");
            }
        },
        {
            field: 'commission_amount', title: '返佣(L)', formatter: function (amount, item) {
                if (amount <= 0) {
                    return '-';
                }
                return format.money(amount, "#7a85f8");
            }
        }
    ]);
    table.setFloatMessage([
        {field: 'main_trade_no', title: '订单号'},
        {field: 'trade_time', title: '交易时间'},
        {field: 'trade_ip', title: 'IP地址'},
        {
            field: 'widget', title: '控件内容', formatter: (widget) => {
                const json = JSON.parse(widget);
                let items = '';
                for (const key in json) {
                    items += `<br><span class="text-primary">${json[key].title}</span>：<span class="text-success fw-bold">${json[key].value}</span>`;
                }
                return items;
            }
        },
    ]);
    table.setDetail([
        {field: 'trade_no', title: '系统订单号'},
        {field: 'item_trade_no', title: '物品订单号'},
        {field: 'main_trade_no', title: '购物订单号'},
        {
            field: 'order_process', title: '进货流程', formatter: (val, item) => {
                const r = util.icon('icon-shuangyoujiantou');
                let html = item.customer_id > 0 ? `${i18n('商家')}(<span class="text-success">${item.customer.username}</span>) ${r} ${i18n('付款')}(${getVar("CCY")}${item.amount}) ${r} 大仓库 ${r} ` : `${i18n('主站')}(${i18n('直营店')}) ${r} ${i18n('大仓库')} ${r} `;
                if (item.user_id > 0 && item.customer_id > 0 && item.user_id === item.customer_id) {
                    html += i18n(`商家自供`);
                } else if (item.user_id > 0) {
                    html += i18n(`付款`) + `(${getVar("CCY")}${item.supply_profit}) ${r} ${i18n('供货商')}(${item.supplier.username})`;
                } else {
                    html += i18n('平台直供');
                }
                return html;
            }
        },
        {
            field: 'commission', title: '返佣记录', formatter: (val) => {
                if (val.length == 0) {
                    return "";
                }
                let log = [];
                val.forEach(item => {
                    log.push(common.renderTableUser(item.user) + ` ${i18n('向上级')} ` + common.renderTableUser(item.parent) + ` ${i18n('返佣')} ` + format.money(item.amount));
                });
                return log.join("<br>");
            }
        },
        {
            field: 'contents', title: '发货内容', formatter: (val, item) => {
                if (item.render === false) {
                    return `<div class="ship-contents order-contents-${item.id}">${val.replaceAll("\n", "<br>")}</div>`;
                }
                util.timer(() => {
                    return new Promise(resolve => {
                        const $handle = $(`.order-contents-${item.id}`);
                        const $refresh = $(`.order-refresh-btn-${item.id}`);
                        if ($handle.length > 0) {
                            $refresh.show();

                            const loadContents = () => {
                                util.post({
                                    url: "/admin/repertory/order/detail",
                                    loader: false,
                                    data: {id: item.id},
                                    done: res => {
                                        $handle.html(res.data.contents);
                                    }
                                });
                            }
                            loadContents();

                            $refresh.click(() => {
                                $handle.html(i18n('正在加载，请稍等..'));
                                loadContents();
                            });

                            resolve(false);
                        } else {
                            resolve(true);
                        }
                    });
                }, 50);
                return `<div class="ship-contents ship-custom-contents order-contents-${item.id}">${i18n('正在加载，请稍等..')}</div><button class="ship-contents-refresh order-refresh-btn-${item.id}"><i class="fa fa-refresh"></i> ${i18n('重新加载')}</button>`;
            }
        },
        {
            field: 'widget', title: '控件内容', formatter: (widget) => {
                const json = JSON.parse(widget);
                let items = [];

                for (const key in json) {
                    items.push({title: json[key].title, content: json[key].value});
                }

                if (items.length == 0) {
                    return '';
                }
                return format.blockItems(items);
            }
        },
        {field: 'trade_time', title: '交易时间'},
        {field: 'trade_ip', title: 'IP地址'},
    ]);
    table.setSearch([
        {
            title: "显示范围：整站", name: "display_scope", type: "select", dict: [
                {id: 1, name: "仅主站"},
                {id: 2, name: "仅供货商"}
            ], change: (search, val) => {
                if (val == 2) {
                    search.show("user_id");
                } else {
                    search.hide("user_id");
                }
            }
        },
        {
            title: "搜索供货商",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=1",
            hide: true,
            change: (search, id, selected) => {
                if (selected) {
                    search.selectReload("equal-repertory_item_id", "repertoryItem?userId=" + id);
                } else {
                    search.selectReload("equal-repertory_item_id", "repertoryItem");
                }
            }
        },
        {
            title: "选择商品",
            name: "equal-repertory_item_id",
            type: "select",
            search: true,
            dict: "repertoryItem",
            change: (select, value) => {
                if (value == "") {
                    select.selectClearOption("equal-repertory_item_sku_id");
                    select.hide("equal-repertory_item_sku_id");
                    return;
                }
                _Dict.advanced("repertoryItemSku?itemId=" + value, res => {
                    select.selectClearOption("equal-repertory_item_sku_id");
                    select.show("equal-repertory_item_sku_id");
                    res.forEach(s => {
                        select.selectAddOption("equal-repertory_item_sku_id", s.id, s.name);
                    });
                })
            }
        },
        {
            title: "SKU",
            name: "equal-repertory_item_sku_id",
            type: "select",
            hide: true
        },
        {title: "系统/物品/商家订单号", name: "trade_no", type: "input", width: 220},
        {title: "模糊搜索控件内容(较慢)", name: "search-widget", type: "input", width: 220},
        {title: "商家", name: "equal-customer_id", type: "remoteSelect", dict: "user?type=2"},
        {title: "交易时间", name: "between-trade_time", type: "date"}
    ]);
    table.setState("status", "repertory_order_status");
    table.onResponse(data => {
        $('.order_count').html(format.color(data.order_count, "#5185fd"));
        $('.order_amount').html(format.color(getVar("CCY") + data.order_amount, "#1ecb45"));
        $('.order_office_profit').html(format.color(getVar("CCY") + data.order_office_profit, "#dbba3c"));
        $('.order_supply_profit').html(format.color(getVar("CCY") + data.order_supply_profit, "#dd782e"));
    });
    table.render();
}();