!function () {
    util.bindButtonUpload(".avatar-file", "/user/upload?mime=image", function (res) {
        $(".image-avatar").attr("src", res.url);
        $(".avatar-input").val(res.url);
    });

    $('.general-save-btn').click(function () {
        const data = util.arrayToObject($('.general-form').serializeArray());
        util.post("/user/security/general/edit", data, res => {
            layer.msg("基本设置已更新");
        });
    });


    $('.send-current-email-code').click(function () {
        util.post({
            url: "/user/security/email/current/code",
            done: res => {
                message.success("验证码已发送，请注意查收");
                util.countDown(this, 60);
            }
        });
    });

    $('.send-new-email-code').click(function () {
        util.post({
            url: "/user/security/email/new/code",
            data: {email: $('input[name=email]').val()},
            done: res => {
                message.success("验证码已发送，请注意查收");
                util.countDown(this, 60);
            }
        });
    });


    $('.email-save-btn').click(function () {
        util.post({
            url: "/user/security/email/bind",
            data: util.arrayToObject($('.email-form').serializeArray()),
            done: res => {
                layer.msg("邮箱更改成功");
                setTimeout(() => {
                    window.location.hash = "#user-email-tab";
                    window.location.reload();
                }, 500);
            }
        });
    });


    $('.passwd-save-btn').click(function () {
        util.post({
            url: "/user/security/password/edit",
            data: util.arrayToObject($('.passwd-form').serializeArray()),
            done: res => {
                layer.msg("密码更改成功");
                setTimeout(() => {
                    window.location.hash = "#user-passwd-tab";
                    window.location.reload();
                }, 500);
            }
        });
    });


    $('.identity-save-btn').click(function () {
        util.post({
            url: "/user/security/identity",
            data: util.arrayToObject($('.identity-form').serializeArray()),
            done: res => {
                layer.msg("身份信息已提交，请等待审核");
                setTimeout(() => {
                    window.location.hash = "#user-identity-tab";
                    window.location.reload();
                }, 500);
            }
        });
    });

    $('.resubmit-identity').click(function () {
        util.post({
            url: "/user/security/identity/resubmit",
            done: res => {
                window.location.hash = "#user-identity-tab";
                window.location.reload();
            }
        });
    });

    $('button[data-bs-toggle=tab]').click(function (){
        location.hash = $(this).attr("id");
    });



    location.hash && $(location.hash).click();
}();