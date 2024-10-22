!function () {
    let payId = 0, isBalance = false, payUrl = localStorage.getItem(`pay_${util.getParam("tradeNo")}`);

    function initPay() {
        let tradeNo = util.getParam("tradeNo");
        pay.getPayOrder(tradeNo, order => {
            pay.timer(tradeNo, () => {
                setTimeout(() => {
                    window.location.href = "/pay/sync." + tradeNo;
                }, 500);
            }, () => {
                setTimeout(() => {
                    window.location.href = "/";
                }, 500);
            }, () => {
                window.location.href = "/";
            });

            pay.expire(order.timeout, time => {
                $payTitle.find(".hour").html(time.hour + i18n("时"));
                $payTitle.find(".minute").html(time.minute + i18n("分"));
                $payTitle.find(".second").html(time.second + i18n("秒"));
            });

            if ([3, 4, 5].includes(order.render_mode)) {
                $renderLoading.hide();
                $payIcon.show();
                $renderQrcode.html("");
                $('.render-qrcode').show(100);
                $renderQrcode.qrcode({
                    render: "canvas",
                    width: 200,
                    height: 200,
                    text: order.pay_url
                });
                $renderPay.find(".block-header").show();
            } else {
                $renderPay.find(`.card-body`).html(`<button type="button" class="btn btn-primary me-1 mb-1 continue-payment"><i class="fa fa-upload opacity-50 me-1"></i> 继续付款</button>`);
                //jump url
                // window.location.href = order.pay_url;
                !payUrl && (payUrl = order.pay_url);
                util.openCheckoutWindowUrl(payUrl);

                $(`.continue-payment`).click(() => {
                    util.openCheckoutWindowUrl(payUrl);
                });
            }
            switch (order.render_mode) {
                case 3: //支付宝
                    $payIcon.html(util.icon("icon-zhifubao") + `<space></space><span class="pay-icon-alipay">${i18n('支付宝')}</span>`);
                    if (util.isMobile()) {
                        let goto = "alipays://platformapi/startapp?appId=20000067&url=" + order.pay_url;
                        $renderQrcode.click(() => {
                            window.location.href = goto;
                        });
                        window.location.href = goto;
                    }
                    break;
                case 4: //微信
                    $payIcon.html(util.icon("icon-weixinzhifu") + `<space></space><span class="pay-icon-wxpay">${i18n('微信支付')}</span>`);
                    break;
                case 5: //QQ
                    $payIcon.html(util.icon("icon-tengxunQQ") + `<space></space><span class="pay-icon-qqpay">${i18n('QQ钱包')}</span>`);
                    break;
            }
        });


    }

    const $payItem = $('.pay-container .online-pay');
    const $balanceClick = $('.wallet-balance-click');
    /*  const $payInput = $('.pay-wrapper input[name=pay_id]');*/
    const $btnCancel = $('.btn-cancel-order');
    const $btnPay = $('.btn-pay-now');
    const $renderPay = $('.render-pay');
    const $renderQrcode = $('.render-qrcode .qrcode-content');
    const $payTitle = $('.render-pay .pay-title');
    const $renderLoading = $('.render-pay .loading');
    const $payIcon = $('.pay-icon');

    function checkCombination() {
        if (isBalance && payId > 0) {
            $btnPay.html(util.icon("icon-chenggong") + " " + i18n("立即付款(组合付款)")).attr("disabled", false);
        } else if (!isBalance && payId == 0) {
            $btnPay.html(util.icon("icon-qingxuanze") + " " + i18n("请选择付款方式")).attr("disabled", true);
        } else {
            $btnPay.html(util.icon("icon-chenggong") + " " + i18n("立即付款")).attr("disabled", false);
        }
    }

    $payItem.click(function () {
        const id = $(this).attr("data-payId");
        $payItem.removeClass("pay-current");
        if (id != payId) {
            payId = id;
            $(this).addClass("pay-current");
        } else {
            payId = 0;
        }

        checkCombination();
    });

    $balanceClick.click(function () {
        isBalance = !isBalance;
        if (isBalance) {
            $(this).addClass("pay-current");
        } else {
            $(this).removeClass("pay-current");
        }

        checkCombination();
    });

    $btnCancel.click(function () {
        util.post("/shop/order/cancel", {trade_no: util.getParam("tradeNo")}, res => {
            window.location.href = "/";
        });
    });

    $btnPay.click(function () {
        util.post("/pay", {trade_no: util.getParam("tradeNo"), method: payId, balance: isBalance ? 1 : 0}, res => {
            localStorage.setItem(`pay_${util.getParam("tradeNo")}`, res.data.pay_url);
            window.location.reload();
        });
    });

    if ($renderPay.length > 0) {
        initPay();
    }
}();