const _user = new class User {
    updateUserInfo() {
        util.post({
            url: "/user/personal/info",
            loader: false,
            done: res => {
                $('.user-withdraw-amount').html(res?.data?.withdraw_amount);
                $('.user-balance-amount').html(res?.data?.balance);
            }
        });
    }
}