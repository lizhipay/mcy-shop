!function () {

    $('.send-email-code').click(function () {
        let email = $('input[name=email]').val();
        util.post("/sendEmail?type=reset", {
            email: email
        }, res => {
            util.countDown(this, 60);
            message.success("验证码发送成功");
        });
    });


    $(".reset-btn").click(function () {
        let map = util.arrayToObject($('#reset-form').serializeArray());
        util.post("/reset", map, res => {
            message.success("您的密码重置，正在前往登录页面..");
            setTimeout(() => {
                window.location.href = "/login";
            }, 1000);
        })
    });
}();