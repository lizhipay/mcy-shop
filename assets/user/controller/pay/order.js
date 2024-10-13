!function () {
    let latestOrderId = 0, updateOrderList = [];

    const $payVoiceBroadcast = $(".pay-voice-broadcast");
    const $monitorOrders = $("#monitorOrders");

    const c = getVoicePack();
    c && $payVoiceBroadcast.val(c);
    $payVoiceBroadcast.change(function () {
        localStorage.setItem("pay-voice-broadcast", this.value);
    });


    function getVoicePack() {
        const c = localStorage.getItem("pay-voice-broadcast");
        if (c != "" && typeof c == "string") {
            return c;
        }
        return false;
    }

    function playVoice(name) {
        const voicePack = getVoicePack();
        if (voicePack) {
            util.loadSound(`/assets/common/audio/order/${voicePack}/${name}.mp3`);
        }
    }

    function closeOrder(id, silent = false) {
        let map = {
            url: "/user/pay/order/close",
            data: {
                id: id
            },
            done: () => {
                table.refresh();
            }
        };
        if (silent) {
            map.loader = false;
            map.error = false;
        }
        util.post(map);
    }


    const table = new Table("/user/pay/order/get", "#pay-order-table");
    table.setPagination(10, [10, 20, 50]);
    table.setColumns([
        {field: 'order.trade_no', title: '订单号'},
        {field: 'customer', title: '会员', formatter: format.customer},
        {field: 'pay', title: '支付方式', formatter: format.category},
        {
            field: 'order_amount', title: '订单金额', formatter: (amount, item) => {
                return format.money(amount, "#19bf5d");
            }
        },
        {
            field: 'balance_amount', title: '余额付款', formatter: (amount, item) => {
                if (amount <= 0) {
                    return '-';
                }

                return format.money(amount, "blue");
            }
        },
        {
            field: 'trade_amount', title: '在线付款', formatter: (amount, item) => {
                if (amount <= 0) {
                    return '-';
                }

                return format.color(amount, "green");
            }
        },
        {field: 'fee', title: '手续费'},
        {field: 'status', title: '支付状态', dict: "pay_order_status"},
        {
            field: 'timeout', title: '过期', formatter: (time, item, index) => {
                let unique = util.generateRandStr(10);
                cache.set(`updateTimeoutToken_${item.id}`, unique);


                if (item.status != 0 && item.status != 1) {
                    return '-';
                }

                let _t = () => {
                    let timer = util.getAbstractTimeout(time);
                    if (timer.expire <= 0) {
                        closeOrder(item.id, true);
                        playVoice("timeout");
                    }
                    return format.color(`${timer.expire}s`, "red");
                }


                const updateTimeout = setInterval(() => {
                    if (cache.get(`updateTimeoutToken_${item.id}`) != unique) {
                        clearInterval(updateTimeout);
                        return;
                    }
                    $(`.${unique}`).html(_t());
                }, 1000);

                updateOrderList.push(item.id);

                return `<span class="${unique}">${_t()}</span>`;
            }
        },
        {
            field: 'operate', class: "nowrap", type: 'button', buttons: [
                {
                    icon: 'icon-update',
                    class: 'acg-badge-h-green',
                    title: '补单',
                    click: (event, value, row, index) => {
                        message.dangerPrompt("您正在执行手动补单操作，此操作将导致订单直接被标记为已支付状态，并触发所有相关分红、返佣、发货等操作。请务必确认已收到货款，否则请勿随意操作，以免引发不可预见的财务风险", "我确认已收到客户钱款", () => {
                            util.post("/user/pay/order/successful", {id: row.id}, res => {
                                message.success("[" + row?.order?.trade_no + "] 补单成功..");
                                table.refresh();
                            });
                        });
                    },
                    show: item => {
                        return [1, 3].includes(item.status) && item.is_custom_pay;
                    }
                }
            ]
        },
    ]);
    table.setFloatMessage([
        {field: 'create_time', title: '下单时间'},
        {field: 'order.create_ip', title: 'IP地址'},
        {field: 'order.create_browser', title: '浏览器'},
        {field: 'order.create_device', title: '设备'},
        {field: 'pay_time', title: '支付时间'},
    ]);
    table.setSearch([
        {title: "支付接口", name: "equal-pay_id", type: "select", dict: "payApi"},
        {
            title: "订单号",
            name: "equal-trade_no",
            type: "input"
        },
        {
            title: "会员",
            name: "equal-customer_id",
            type: "remoteSelect",
            dict: "customer"
        },
        {title: "下单时间", name: "between-create_time", type: "date"},

    ]);
    table.setState("status", "pay_order_status");
    table.onResponse(data => {
        updateOrderList = [];

        const token = util.generateRandStr(10);

        cache.set("payOrderTimerToken", token);

        util.timer(() => {
            return new Promise(resolve => {
                if (!$monitorOrders.is(':checked')) {
                    resolve(token === cache.get("payOrderTimerToken"));
                    return;
                }

                if (token != cache.get("payOrderTimerToken")) {
                    resolve(false);
                    return;
                }

                util.post({
                    url: "/user/pay/order/getLatestOrderId",
                    loader: false,
                    error: false,
                    done: res => {
                        if (res.data.id != latestOrderId && latestOrderId != 0) {
                            table.refresh();
                            playVoice("pay");
                        }
                        latestOrderId = res.data.id;
                        resolve(true);
                    }
                });
            });
        }, 1500);


        util.timer(() => {
            return new Promise(resolve => {
                if (!$monitorOrders.is(':checked')) {
                    resolve(token === cache.get("payOrderTimerToken"));
                    return;
                }

                if (token != cache.get("payOrderTimerToken")) {
                    resolve(false);
                    return;
                }

                util.post({
                    url: "/user/pay/order/status",
                    data: {
                        list: updateOrderList
                    },
                    loader: false,
                    error: false,
                    done: res => {
                        if (res.data.status === true) {
                            table.refresh();
                            playVoice("success");
                        }
                        resolve(token === cache.get("payOrderTimerToken"));
                    }
                });
            });
        }, 1500);

        $('.data-count .order-count').html(data.data.order_count);
        $('.data-count .trade-amount').html(getVar("CCY") + data.data.trade_amount);
        $('.data-count .balance-amount').html(getVar("CCY") + data.data.balance_amount);
    });
    table.render();
}();