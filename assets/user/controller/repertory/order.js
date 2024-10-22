!function () {
    const table = new Table("/user/repertory/order/get", "#repertory-order-table");
    table.setPagination(10, [10, 20, 30]);
    table.setColumns([
        {field: 'main_trade_no', title: '订单号'},
        {
            field: 'item.name', title: 'SKU/商品', formatter: (name, item) => {
                return `‹<small>${item?.sku?.name}</small>›` + name;
            }
        },
        {
            field: 'amount', title: '订单金额', formatter: function (amount, item) {
                if (amount == 0) {
                    return '-';
                }

                if (item.is_self_operated === true) {
                    return format.money(amount, "#45bf77") + `(${format.color("自营税" , "red")})`;
                }

                return format.money(amount, "#45bf77");
            }
        },
        {field: 'quantity', title: '数量'},
        {field: 'status', title: '状态', dict: "repertory_order_status"},
        {
            field: 'is_self_operated', title: '订单类型', formatter: is => {
                if (is === true) {
                    return format.cambridgeBlue('自营商城');
                }

                return format.success('仓库出货');
            }
        },
        {
            field: 'supply_profit', title: '盈利', formatter: function (supplyProfit, item) {
                if (supplyProfit == 0 || ![1, 2].includes(item.status)) {
                    return "-";
                }
                return format.money(supplyProfit, "#16d12c");
            }
        },
        {
            field: 'item_cost', title: '成本', formatter: function (itemCost, item) {
                if (itemCost <= 0) {
                    return '-';
                }
                return format.money(itemCost, "#fd5050");
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
    table.setSearch([
        {title: "订单号", name: "equal-main_trade_no", type: "input", width: 220},
        {title: "模糊搜索控件内容(较慢)", name: "search-widget", type: "input", width: 220},
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
        {title: "交易时间", name: "between-trade_time", type: "date"}
    ]);
    table.onResponse(data => {
        $('.order_count').html(format.color(data.order_count, "#5185fd"));
        $('.order_amount').html(format.color(getVar("CCY") + data.order_amount, "#1ecb45"));
        $('.order_supply_profit').html(format.color(getVar("CCY") + data.order_supply_profit, "#dd782e"));
    });
    table.setDetail([
        {field: 'trade_no', title: '系统订单号'},
        {field: 'item_trade_no', title: '物品订单号'},
        {field: 'main_trade_no', title: '购物订单号'},
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
                                    url: "/user/repertory/order/detail",
                                    loader: false,
                                    data: {id: item.id},
                                    done: res => {
                                        $handle.html(res.data.contents);
                                    }
                                });
                            }
                            loadContents();

                            $refresh.click(() => {
                                $handle.html(`正在加载，请稍等...`);
                                loadContents();
                            });

                            resolve(false);
                        } else {
                            resolve(true);
                        }
                    });
                }, 50);
                return `<div class="ship-contents ship-custom-contents order-contents-${item.id}">正在加载，请稍等...</div><button class="ship-contents-refresh order-refresh-btn-${item.id}"><i class="fa fa-refresh"></i> 重新加载</button>`;
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
    table.render();
}();