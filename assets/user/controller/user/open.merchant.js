!function () {

    $(".open-merchant").click(function () {
        const groupId = $(this).attr("data-id");
        const amount = $(this).attr("data-amount");
        const group = $(this).attr("data-group");

        const msg = amount > 0 ? `您正在开通商家权限<b class="text-warning">「${group}」</b>，需要支付<b class="text-success">${getVar("CCY")}${amount}</b>，一旦开通成功，无法退款，是否继续？` : `您正在开通商家权限<b class="text-warning">「${group}」</b>，此权限可免费开通，是否继续？`;


        message.ask(msg, () => {
            if (amount > 0) {
                pay.openPayment("group", amount, (isBalance, payId) => {
                    util.post("/user/merchant/open/trade", {group_id: groupId}, res => {
                        pay.payment(payId, isBalance, res.data.trade_no);
                    });
                });
            } else {
                util.post("/user/merchant/open/trade", {group_id: groupId}, res => {
                    if (res?.data?.is_free === true) {
                        layer.msg("恭喜您，商家权限已开通成功！");
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        layer.msg("开通失败，请稍后再试！");
                    }
                });
            }
        }, "确认开通商家", "立即开通");
    });

}();