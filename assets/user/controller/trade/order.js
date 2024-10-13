!function () {
    const reportApply = item => {
        component.popup({
            submit: '/user/report/order/apply',
            confirmText: util.icon("icon-anquan4") + " " + i18n("提交申请"),
            tab: [
                {
                    name: util.icon("icon-anquan4") + " 申请售后/维权",
                    form: [
                        {
                            name: "id",
                            type: "input",
                            default: item.id,
                            hide: true
                        },
                        {
                            title: "维权原因",
                            name: "type",
                            type: "select",
                            placeholder: "请选择",
                            dict: "order_report_type",
                            default: 0,
                            required: true
                        },
                        {
                            title: "维权方式",
                            name: "expect",
                            type: "select",
                            placeholder: "请选择",
                            dict: "order_report_expect",
                            default: 0,
                            required: true
                        },
                        {
                            title: "维权内容",
                            name: "message",
                            type: "textarea",
                            placeholder: "请详细说明您维权的原因",
                            height: 260,
                            required: true
                        },
                        {
                            title: "相关截图",
                            name: "image_url",
                            type: "image",
                            placeholder: "请选择与订单相关的截图",
                            uploadUrl: '/user/upload',
                            photoAlbumUrl: '/user/upload/get',
                            height: 200
                        }
                    ]
                }
            ],
            done: () => {
                window.location.href = "/user/report/order?tradeNo=" + item.main_trade_no;
            },
            autoPosition: true,
        });
    }
    const table = new Table("/user/trade/order/get", "#trade-order-table");
    table.setPagination(12, [12, 20, 50, 100]);
    table.setColumns([
        {field: 'main_trade_no', title: '订单号'},
        {field: 'item', title: '商品名称', formatter: format.item},
        {field: 'sku', title: '商品类型', formatter: format.item},
        {
            field: 'amount', title: '金额', align: 'center', formatter: amount => {
                return format.money(amount, "#19bf5d");
            }
        },
        {field: 'quantity', title: '数量', align: 'center'},
        {
            field: 'status',
            title: '状态',
            align: 'center',
            formatter: (status, item) => {
                if (item.pay_status != 1) {
                    return format.danger(i18n("未付款"));
                }
                return _Dict.result("shop_order_item_status", status);
            }
        },
        {field: 'create_time', title: '订单时间'},
        {
            field: 'treasure', title: '宝贝信息', class: 'treasure', type: 'button', buttons: [
                {
                    icon: 'icon-chakanxiangqing',
                    class: 'no-btn',
                    tips: "宝贝内容",
                    click: (event, value, row, index) => {
                        util.post("/user/trade/order/item", {id: row.id}, res => {
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
                        window.open(`/user/trade/order/download?orderId=${row.id}`);
                    },
                    show: item => {
                        return [1, 3, 4].includes(item.status) && !item.render;
                    }
                }
            ]
        },
        {
            field: 'trade', title: '交易操作', class: 'trade-content', type: 'button', buttons: [
                {
                    icon: 'icon-querenshouhuo',
                    class: 'acg-badge-h-dodgerblue',
                    title: '确认收货<span class="timeout" style="color: #a3a3a3;font-size: 14px;"></span>',
                    click: (event, value, row, index) => {
                        message.ask("请确认您是否已经收到宝贝。确认收货后，订单将视为完成交易，届时您将无法申请售后维权服务。", () => {
                            util.post("/user/trade/order/receipt", {id: row.id}, res => {
                                table.refresh(true);
                            });
                        });
                    },
                    show: item => {
                        if ([1, 2, 4].includes(item.status)) {
                            util.timer(() => {
                                return new Promise(resolve => {
                                    let $handle = $(`.trade-content span[data-id=${item.id}] .timeout`);
                                    if ($handle.length > 0) {
                                        const time = util.getAbstractTimeout(item.auto_receipt_time);
                                        let html = "(还剩";
                                        if (time.expire >= 86400) {
                                            html += `${time.day}天`;
                                        } else if (time.expire >= 3600) {
                                            html += `${time.hour}小时`;
                                        } else if (time.expire >= 60) {
                                            html += `${time.minute}分`;
                                        } else {
                                            html += `${time.expire}秒`;
                                        }
                                        html += ")";
                                        $handle.html(html);
                                        resolve(false);
                                        return;
                                    }
                                    resolve(true);
                                });
                            }, 20, true);
                            return true;
                        }
                        return false;
                    }
                },
                {
                    icon: 'icon-anquan4',
                    class: 'btn-outline-dodgerblue',
                    title: '申请售后/维权',
                    click: (event, value, row, index) => {
                        reportApply(row);
                    },
                    show: item => {
                        return [0, 1, 2].includes(item.status) && item.pay_status == 1;
                    }
                },
                {
                    icon: 'icon-anquan4',
                    class: 'btn-outline-dodgerblue',
                    title: '查看维权',
                    click: (event, value, row, index) => {
                        window.location.href = "/user/report/order?tradeNo=" + row.main_trade_no;
                    },
                    show: item => {
                        return item.status == 4;
                    }
                },
            ]
        },
    ]);
    table.setSearch([
        {
            title: "输入商品标题或订单号",
            name: "keywords",
            type: "input",
            width: 260
        },
        {title: "下单时间", name: "between-create_time", type: "date"}
    ]);
    table.setState("status", "shop_order_item_status");
    table.render();
}();