!function () {
    const table = new Table("/admin/shop/order/item/get", "#shop-order-item-table");
    table.setPagination(10, [10, 20, 50, 100]);
    table.setColumns([
        {
            field: 'order.user', title: '商家信息', formatter: format.user
        },
        {field: 'order.customer', title: '会员', formatter: format.customer},
/*        {
            field: 'order.pay_order.status', title: '支付状态', formatter: (status, item) => {
                return _Dict.result("pay_order_status", status);
            }
        },*/
        {
            field: 'amount', title: '金额', align: 'center', formatter: amount => {
                return format.money(amount, "#19bf5d");
            }
        },
        {field: 'quantity', title: '数量'},
        {
            field: 'item.name', title: 'SKU/商品', formatter: (name, item) => {
                return `‹<small>${item?.sku?.name}</small>›` + name;
            }
        },
        {
            field: 'order.pay_order.pay', title: '支付方式', align: 'center', formatter: format.category
        },
        {
            field: 'status',
            title: '状态',
            align: 'center',
            formatter: (status, item) => {
                if (item?.order?.status == 0) {
                    return format.primary(util.icon("icon-round-loading") + " 正在提交支付");
                }
                if (item?.order?.status == 3) {
                    return _Dict.result("pay_order_status", item?.order?.pay_order?.status);
                }

                if (status == 1) {
                    const time = util.getAbstractTimeout(item.auto_receipt_time);
                    let html = "";
                    if (time.expire >= 86400) {
                        html += `${time.day}天`;
                    } else if (time.expire >= 3600) {
                        html += `${time.hour}小时`;
                    } else if (time.expire >= 60) {
                        html += `${time.minute}分`;
                    } else {
                        html += `${time.expire}秒`;
                    }
                    return format.success(util.icon("icon-shalou") + " " + html + "后自动收货");
                }

                return _Dict.result("shop_order_item_status", status);
            }
        },
        {
            field: 'treasure', class: 'treasure nowrap',  type: 'button', buttons: [
                {
                    icon: 'icon-chakanxiangqing',
                    class: 'no-btn',
                    tips: "宝贝内容",
                    click: (event, value, row, index) => {
                        util.post("/admin/shop/order/item", {id: row.id}, res => {
                            treasure.show(res.data);
                        });
                    },
                    show: item => {
                        return [1, 3, 4].includes(item.status);
                    }
                },
                {
                    icon: 'icon-fuzhi',
                    class: 'no-btn',
                    tips: "复制宝贝",
                    click: (event, value, row, index) => {
                        util.copyTextToClipboard(row.treasure, () => {
                            layer.msg("复制成功");
                        }, () => {
                            layer.msg("复制失败");
                        });
                    },
                    show: item => {
                        return [1, 3, 4].includes(item.status) && !item.render;
                    }
                },
                {
                    tips: "下载宝贝到本地",
                    icon: 'icon-yunxiazai',
                    class: 'no-btn',
                    click: (event, value, row, index) => {
                        window.open(`/admin/shop/order/download?orderId=${row.id}`);
                    },
                    show: item => {
                        return [1, 3, 4].includes(item.status) && !item.render;
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
                    search.show("equal-item_id");
                } else {
                    search.hide("equal-item_id");
                }

                search.hide("equal-sku_id");
                search.selectClearOption("equal-sku_id");
                search.selectReload("equal-item_id", "item");
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
                    search.show("equal-item_id");
                    search.selectReload("equal-item_id", "item?userId=" + id);
                } else {
                    search.hide("equal-item_id");
                    search.selectReload("equal-item_id", "item");
                }
            }
        },
        {
            title: "选择商品",
            name: "equal-item_id",
            type: "select",
            search: true,
            dict: "item",
            hide: true,
            change: (select, value) => {
                if (value == "") {
                    select.selectClearOption("equal-sku_id");
                    select.hide("equal-sku_id");
                    return;
                }
                _Dict.advanced("itemSku?itemId=" + value, res => {
                    select.selectClearOption("equal-sku_id");
                    select.show("equal-sku_id");
                    res.forEach(s => {
                        select.selectAddOption("equal-sku_id", s.id, s.name);
                    });
                })
            },
            complete: (search) => {
                search.hide("equal-sku_id");
            }
        },
        {
            title: "SKU",
            name: "equal-sku_id",
            type: "select",
            hide: true
        },
        {
            title: "订单号",
            name: "equal-trade_no",
            type: "input",
            width: 230
        },
        {
            title: "会员",
            name: "equal-customer_id",
            type: "remoteSelect",
            dict: "user"
        },
        {title: "下单时间", name: "between-create_time", type: "date"},
        {
            title: "IP地址",
            name: "equal-create_ip",
            type: "input"
        },
    ]);
    table.setState("status", "shop_order_item_status");
    table.setFloatMessage([
        {field: 'order.trade_no', title: '购物订单号'},
        {field: 'trade_no', title: '物品订单号'},
        {
            field: 'order.pay_order', title: '支付订单', formatter: (order, item) => {
                if (!order) {
                    return null;
                }
                return `<a href="/admin/pay/order?tradeNo=${item.order.trade_no}" target="_blank" class="text-primary">查看订单</a>`;
            }
        },
/*        {
            field: 'order.pay_order.pay', title: '支付方式', formatter: pay => {
                return format.pay(pay);
            }
        },*/
        {
            field: 'order.pay_order.pay_url', title: '支付地址', formatter: (url) => {
                if (!url) {
                    return null;
                }
                return `<a href="${url}" target="_blank" class="text-primary">${url}</a>`;
            }
        },
        {field: 'order.pay_order.pay_time', title: '支付时间'},
        {
            field: 'refund_mode', title: '退款模型', formatter: (mode, item) => {
                return _Dict.result("item_refund_mode", mode);
            }
        },
        {
            field: 'order.invite', title: '推广者', formatter: (invite, item) => {
                return format.invite(invite);
            }
        },
        {
            field: 'dividend_amount', title: '分红', formatter: amount => {
                if (amount > 0) {
                    return format.money(amount, "#6960ff");
                }
                return '-';
            }
        },
        {field: 'order.create_ip', title: '下单IP'},
        {field: 'order.create_browser', title: '浏览器'},
        {field: 'order.create_device', title: '设备型号'},
        {
            field: 'widget', title: '控件内容', formatter: (widget) => {
                if (typeof widget != "object"){
                    return '';
                }
                let items = '';
                for (const key in widget) {
                    items += `<br><span class="text-primary">${widget[key].title}</span>：<span class="text-success fw-bold">${widget[key].value}</span>`;
                }
                return items;
            }
        },
    ]);
    table.onResponse(data => {
        $('.data-count .order-count').html(data.order_count);
        $('.data-count .order-amount').html(getVar("CCY") + data.order_amount);
    });
    table.render();
}();