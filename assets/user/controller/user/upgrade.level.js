!function () {

    $(".upgrade-level").click(function () {
        const levelId = $(this).attr("data-id");
        const amount = $(this).attr("data-amount");
        const name = $(this).attr("data-name");


        message.ask(`您正在升级会员等级<b class="text-warning">「${name}」</b>，需要支付<b class="text-success">${getVar("CCY")}${amount}</b>，一旦升级成功，无法退款，是否继续？`, () => {
            pay.openPayment("level", amount, (isBalance, payId) => {
                util.post("/user/level/trade", {level_id: levelId}, res => {
                    pay.payment(payId, isBalance, res.data.trade_no);
                });
            });
        }, "确认付费升级", "立即升级");
    });

}();