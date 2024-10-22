!function () {
    let payId = $('.pay-current').attr("data-payId");

    function switchPay($this, $payItem, $input) {
        payId = $this.attr("data-payId");
        $payItem.removeClass("pay-current");
        $this.addClass("pay-current");
    }

    const $payItem = $('.pay-list .pay-item');
    const $rechargeAmount = $('.recharge-amount');
    const $btnPay = $('.btn-recharge');


    $payItem.click(function () {
        switchPay($(this), $payItem);
    });

    $btnPay.click(function () {
        util.post("/user/recharge/trade", {amount: $rechargeAmount.val()}, res => {
            //拉起支付
            util.post("/pay", {trade_no: res.data.trade_no, method: payId}, result => {
                localStorage.setItem(`pay_${res?.data?.trade_no}`, result?.data?.pay_url);
                window.location.href = `/checkout?tradeNo=${res?.data?.trade_no}`;
            });
        });
    });
}();