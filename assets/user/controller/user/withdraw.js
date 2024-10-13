!function () {
    $(".bank-select").select2({
        templateResult: format.select2BankCard,
        templateSelection: format.select2BankCard,
        theme: 'bootstrap-5',
        minimumResultsForSearch: Infinity
    });


    $('.btn-recharge').click(() => {
        const cardId = $('.bank-select').val();
        const amount = $('.withdraw-amount').val();


        util.post("/user/withdraw/apply", {card_id: cardId, amount: amount}, () => {
            message.alert("提现成功，银行处理中", "success");
            _user.updateUserInfo();
            table.refresh();
        });
    });


    const table = new Table("/user/withdraw/get", "#user-withdraw-table");
    table.setColumns([
        {field: 'trade_no', title: '流水号'},
        {
            field: 'card', title: '银行卡', formatter: format.bankCard
        },
        {
            field: 'amount', title: '提现金额', formatter: format.amount
        },
        {field: 'status', title: '提现状态', dict: 'user_withdraw_status'},
        {field: 'handle_message', title: '银行消息'},
        {field: 'create_time', title: '提现时间'},
        {
            field: 'handle_time', title: '到账时间', formatter: (time, item) => {
                if (item.status != 1) {
                    return '-';
                }
                return time;
            }
        }
    ]);
    table.setPagination(5, [5, 10, 20]);
    table.render();

}();