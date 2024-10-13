!function () {
    $(".login-btn").click(function () {
        let map = util.arrayToObject($('#login-form').serializeArray());
        let _this = $(this);

        util.post("/login", map, res => {
            _this.attr("disabled", true);
            message.success("登录成功，正在跳转..");
            localStorage.setItem("user_token", res.data.token);
            window.location.href = util.getParam("goto") !== null ? decodeURIComponent(util.getParam("goto")) : "/";
        })
    });


    $(document).keydown(function (event) {
        if (event.key === 'Enter') {
            $(".login-btn").click();
        }
    });
}();