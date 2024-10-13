!function () {


    const tmp = new Table("/user/shop/order/get", "#shop-order-table");
    tmp.setPagination(12, [12, 20, 50, 100]);
    tmp.setColumns([
        {field: 'order.customer', title: '会员', formatter: format.customer},

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
            field: 'treasure', class: 'treasure nowrap', type: 'button', buttons: [
                {
                    icon: 'icon-chakanxiangqing',
                    class: 'no-btn',
                    tips: "宝贝内容",
                    click: (event, value, row, index) => {
                        util.post("/user/shop/order/item", {id: row.id}, res => {
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
                        window.open(`/user/shop/order/download?orderId=${row.id}`);
                    },
                    show: item => {
                        return [1, 3, 4].includes(item.status) && !item.render;
                    }
                }
            ]
        },
    ]);
    tmp.setFloatMessage([
        {field: 'order.trade_no', title: '购物订单号'},
        {field: 'trade_no', title: '物品订单号'},
        {
            field: 'order.pay_order', title: '支付订单', formatter: (order, item) => {
                if (!order) {
                    return null;
                }
                return `<a href="/user/pay/order?tradeNo=${item.order.trade_no}" target="_blank" class="text-primary">查看订单</a>`;
            }
        },
/*        {
            field: 'order.pay_order.status', title: '支付状态', formatter: (status, item) => {
                return _Dict.result("pay_order_status", status);
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
    ]);
    tmp.setSearch([
        {
            title: "选择商品",
            name: "equal-item_id",
            type: "select",
            search: true,
            dict: "item",
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
            title: "联系方式",
            name: "equal-contact",
            type: "input"
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
    tmp.setState("status", "shop_order_item_status");
    tmp.onResponse(data => {
        $('.data-count .order-count').html(data.order_count);
        $('.data-count .order-amount').html(getVar("CCY") + data.order_amount);
    });
    tmp.render();
}();