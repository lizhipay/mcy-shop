!function () {
    const table = new Table("/user/shop/supply/order/get", "#supply-order-table");
    table.setPagination(10, [10, 20, 30]);
    table.setColumns([
        {field: 'main_trade_no', title: '订单号'},
        {
            field: 'item.name', title: 'SKU/商品', formatter: (name, item) => {
                return `‹<small>${item?.sku?.name}</small>›` + name;
            }
        },
        {
            field: 'amount', title: '费用', formatter: function (amount, item) {
                if (amount == 0) {
                    return '-';
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
        {title: "交易时间", name: "between-trade_time", type: "date"}
    ]);
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
                                    url: "/user/shop/supply/order/detail",
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