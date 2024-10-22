const pay = new class Pay {
    getPayOrder(tradeNo, done = null) {
        util.post({
            url: "/pay/getOrder",
            loader: {
                enable: false
            },
            data: {trade_no: tradeNo},
            done: res => {
                const order = res.data;
                typeof done === "function" && done(order);
            },
            error: () => {
                window.location.href = "/";
            }
        });
    }

    timer(tradeNo, done, timeout, error) {
        util.timer(() => {
            return new Promise(resolve => {
                this.getPayOrder(tradeNo, order => {
                    if (order.status === 2) {
                        if (new Date() > new Date(order.timeout)) {
                            //超时
                            message.error("订单支付超时");
                            typeof timeout == "function" && timeout();
                            resolve(false);
                            return;
                        }
                        message.alert("支付成功", "success");
                        //支付成功
                        typeof done == "function" && done();
                        resolve(false);
                        return;
                    } else if (order.status === 3) {
                        typeof error == "function" && error();
                        resolve(false);
                        return;
                    }

                    resolve(true);
                });
            });
        }, 2000);
    }

    expire(timeout, done = null) {
        let timer = 0;
        let callback = () => {
            let timestamp = new Date(timeout).getTime() / 1000;
            let now_timestamp = parseInt(new Date().getTime() / 1000);
            let expire = parseInt(timestamp) - now_timestamp;
            let day = Math.floor(expire / (24 * 3600)); // Math.floor()向下取整
            let hour = Math.floor((expire - day * 24 * 3600) / 3600);
            let minute = Math.floor((expire - day * 24 * 3600 - hour * 3600) / 60);
            let second = expire - day * 24 * 3600 - hour * 3600 - minute * 60;
            let cc = {
                expire: expire,
                day: day,
                hour: hour,
                minute: minute,
                second: second
            }
            typeof done === 'function' && done(cc);
            if (expire <= 0) {
                clearInterval(timer);
            }
        }
        callback();
        timer = setInterval(() => {
            callback();
        }, 1000);
    }


    openPayment(business, amount, callback) {
        let isBalance = true, payId = 0;
        component.popup({
            submit: false,
            confirmText: "付款",
            autoPosition: true,
            width: "520px",
            tab: [
                {
                    name: util.icon("icon--yue") + " 付款",
                    form: [
                        {
                            title: false,
                            name: "custom",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(`<div class="pay-container">
<div class="layout-box wallet-balance-box">
                    <div class="title">余额支付 <span class="text-success wallet-balance">0.00</span></div>
                    <div class="pay-list balance-pay"></div>
                </div>

<div class="layout-box online-pay-box">
                    <div class="title">在线支付</div>
                    <div class="pay-list online-pay"></div>
                </div>
                <div class="layout-box">
                    <button type="button" class="btn-pay">确认付款（${getVar("CCY")}${amount}）</button>
                </div>
</div>`);
                                const $onlinePay = dom.find(".online-pay");
                                const $balancePay = dom.find(".balance-pay");
                                const $walletBalance = dom.find(".wallet-balance");
                                const $btnPay = dom.find(".btn-pay");
                                util.post({
                                    url: "/pay/list",
                                    data: {
                                        business: business,
                                        amount: amount
                                    },
                                    done: res => {
                                        if (res.is_login == false || res.balance <= 0) {
                                            $('.wallet-balance-box').hide();
                                            isBalance = false;
                                        }

                                        if (res?.data?.length == 0) {
                                            $(`.online-pay-box`).hide();
                                        }

                                        $walletBalance.html(getVar("CCY") + res.balance);
                                        $balancePay.append(`<div class="pay-item pay-current wallet-balance-click"><img src="/assets/common/images/balance.png"><span>我的钱包</span></div>`);
                                        res.data.forEach((item, index) => {
                                            $onlinePay.append(`<div data-payId="${item.id}" class="pay-item online-pay-click ${(res.is_login == false || res.balance <= amount) ? (index == 0 ? "pay-current" : "") : ""}"><img src="${item.icon}"><span>${item.name}</span></div>`);
                                            if ((res.is_login == false || res.balance <= amount) && index == 0) {
                                                payId = item.id;
                                            }
                                        });

                                        function checkCombination() {
                                            if (isBalance && payId > 0) {
                                                const payAmount = (new Decimal(amount)).sub(res.balance).getAmount(2);
                                                $btnPay.html(`${payAmount > 0 ? "在线支付" : "确认付款"}（${getVar("CCY")}${payAmount > 0 ? payAmount : amount}）`).attr("disabled", false);
                                            } else if (!isBalance && payId == 0) {
                                                $btnPay.html("请选择付款方式").attr("disabled", true);
                                            } else if (isBalance && payId == 0) {
                                                const enough = parseFloat(res.balance) >= parseFloat(amount);
                                                $btnPay.html(enough ? `确认付款（${getVar("CCY")}${amount}）` : "余额不足").attr("disabled", !enough);
                                            } else {
                                                $btnPay.html(`在线支付（${getVar("CCY")}${amount}）`).attr("disabled", false);
                                            }
                                        }

                                        checkCombination();

                                        $('.wallet-balance-click').click(function () {
                                            isBalance = !isBalance;
                                            if (isBalance) {
                                                $(this).addClass("pay-current");
                                            } else {
                                                $(this).removeClass("pay-current");
                                            }

                                            checkCombination();
                                        });

                                        $('.online-pay-click').click(function () {
                                            const id = $(this).attr("data-payId");
                                            $(".online-pay-click").removeClass("pay-current");

                                            if (id != payId) {
                                                payId = $(this).attr("data-payId");
                                                $(this).addClass("pay-current");
                                            } else {
                                                payId = 0;
                                            }

                                            checkCombination();
                                        });

                                        $btnPay.click(function () {
                                            typeof callback === "function" && callback(isBalance, payId);
                                        });
                                    }
                                });
                            }
                        }
                    ]
                }
            ],
            maxmin: false,
            shadeClose: true,
            assign: {}
        });
    }


    payment(payMethod, isBalance, tradeNo) {
        //拉起支付
        util.post("/pay", {trade_no: tradeNo, method: payMethod, balance: isBalance ? 1 : 0}, result => {
            // window.location.href = result.data.pay_url; //跳转到支付页面
            /*  util.openCheckoutWindowUrl(result.data.pay_url);
              pay.timer(tradeNo, () => {
                  setTimeout(() => {
                      window.location.reload();
                  }, 500);
              }, () => {
                  setTimeout(() => {
                      window.location.reload();
                  }, 500);
              }, () => {
                  window.location.reload();
              });*/

            localStorage.setItem(`pay_${tradeNo}`, result?.data?.pay_url);
            window.location.href = `/checkout?tradeNo=${tradeNo}`;
        });
    }
}