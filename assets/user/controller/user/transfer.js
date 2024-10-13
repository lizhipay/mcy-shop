!function () {
    const $amount = $('.recharge-amount');
    const $btnTransfer = $('.btn-recharge');
    const $transferPayee = $('.transfer-payee');
    const $balance = $('.recharge-view .balance');


    $btnTransfer.click(function () {
        message.ask("资金一旦转出则无法追回。", () => {
            util.post("/user/transfer/to", {payee: $transferPayee.val(), amount: $amount.val()}, res => {
                $balance.html(res.data.balance);
                message.alert("转账成功", "success");
            });
        });
    });
}();