!function () {
    $('.send-email-code').click(function () {
        let email = $('input[name=email]').val();
        util.post("/sendEmail?type=register", {
            email: email
        }, res => {
            util.countDown(this, 60);
            message.success("验证码发送成功");
        });
    });


    $(".register-btn").click(function () {
        let map = util.arrayToObject($('#register-form').serializeArray());

        if ($('input[name=terms]').length > 0 && map['terms'] != 1) {
            layer.msg('请先同意用户协议');
            return;
        }

        util.post("/register", map, res => {
            message.success("注册成功，正在登录..");
            window.location.href = util.getParam("goto") !== null ? decodeURIComponent(util.getParam("goto")) : "/";
        })
    });
}();