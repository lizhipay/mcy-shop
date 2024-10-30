!function () {
    const version = getVar("version");


    function handleAutoReceipt() {
        const token = util.generateRandStr(16), tokenKey = "_admin_task_token";
        localStorage.setItem(tokenKey, token);

        util.debug("自动收货TASK->启动..", "#98FB98");
        util.timer(() => {
            return new Promise(resolve => {
                if (token != localStorage.getItem(tokenKey)) {
                    util.debug("自动收货TASK->检测到其他窗口任务启动，已自动关闭当前任务进程..", "#00BFFF");
                    resolve(false);
                    return;
                }
                util.debug("自动收货TASK->开始更新..", "#FF8C00");
                util.post({
                    url: "/admin/task/autoReceipt",
                    loader: false,
                    done: () => {
                        util.debug("自动收货TASK->更新完成!", "#00FA9A");
                        resolve(true);
                    },
                    error: () => {
                        util.debug("自动收货TASK->更新失败!", "#FF5733");
                        resolve(false);
                    }
                });
            });
        }, 60000, true);
    }


    function handPasswordEdit() {
        $('.password-edit').click(function () {
            const avatar = $(this).attr("data-avatar");
            component.popup({
                submit: '/admin/personal/edit',
                tab: [
                    {
                        name: util.icon("icon-anquan3") + " 安全设置",
                        form: [
                            {
                                title: "头像",
                                name: "avatar",
                                type: "image",
                                placeholder: "请选择图片头像",
                                uploadUrl: '/admin/upload',
                                height: 64,
                                default: avatar
                            },
                            {
                                title: "重置登录密码",
                                name: "reset_password",
                                type: "switch",
                                change: (form, value) => {
                                    if (value) {
                                        form.show("current_password");
                                        form.show("new_password");
                                        form.show("re_new_password");
                                    } else {
                                        form.hide("current_password");
                                        form.hide("new_password");
                                        form.hide("re_new_password");
                                        form.setInput("current_password", "");
                                        form.setInput("new_password", "");
                                        form.setInput("re_new_password", "");
                                    }
                                }
                            },
                            {
                                title: "当前登录密码",
                                name: "current_password",
                                type: "password",
                                placeholder: "请填写您当前的登录密码",
                                hide: true
                            },
                            {
                                title: "新密码",
                                name: "new_password",
                                type: "password",
                                placeholder: "请填写新的密码",
                                hide: true
                            },
                            {
                                title: "确认新密码",
                                name: "re_new_password",
                                type: "password",
                                placeholder: "请再次输入新的密码",
                                hide: true
                            },
                        ]
                    }
                ],
                shadeClose: true,
                autoPosition: true,
                maxmin: false,
                width: "520px",
                assign: {},
                done: () => {
                    setTimeout(() => {
                        window.location.reload();
                    }, 700);
                }
            });
        });
    }


    const topUp = () => {
        component.popup({
            submit: false,
            confirmText: "充值",
            autoPosition: true,
            width: "520px",
            tab: [
                {
                    name: util.icon("icon--yue") + " 应用商店-钱包充值",
                    form: [
                        {
                            title: false,
                            name: "custom",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(`<div class="pay-container">               
<div class="layout-box">
                    <div class="title">充值金额</div>
                    <div class="pay-list balance-pay"><input type="text" class="store-recharge-amount" placeholder="最低10元起充" value="100"></div>
                </div>

<div class="layout-box">
                    <div class="title">在线支付</div>
                    <div class="pay-list online-pay"></div>
                </div>
                <div class="layout-box">
                    <button type="button" class="btn-pay">立即充值</button>
                </div>
</div>`);
                                const $onlinePay = dom.find(".online-pay");
                                const $balancePay = dom.find(".balance-pay");
                                const $btnPay = dom.find(".btn-pay");
                                util.post({
                                    url: "/admin/store/pay/list", done: res => {
                                        let payId = res.data[0].id;

                                        res.data.forEach((item, index) => {
                                            $onlinePay.append(`<div data-payId="${item.id}" class="pay-item ${index == 0 ? "pay-current" : ""} online-pay-click"><img src="${item.icon}"><span>${item.name}</span></div>`);
                                        });

                                        function checkCombination() {
                                            if (payId > 0) {
                                                const payAmount = (new Decimal(amount)).sub(res.balance).getAmount(2);
                                                $btnPay.html(`${payAmount > 0 ? "在线支付" : "确认付款"}（￥${payAmount > 0 ? payAmount : amount}）`).attr("disabled", false);
                                            } else if (!isBalance && payId == 0) {
                                                $btnPay.html("请选择付款方式").attr("disabled", true);
                                            } else if (isBalance && payId == 0) {
                                                const enough = parseFloat(res.balance) >= parseFloat(amount);
                                                $btnPay.html(enough ? `确认付款（￥${amount}）` : "余额不足").attr("disabled", !enough);
                                            } else {
                                                $btnPay.html(`在线支付（￥${amount}）`).attr("disabled", false);
                                            }
                                        }


                                        $onlinePay.find('.online-pay-click').click(function () {
                                            payId = $(this).attr("data-payId");
                                            $onlinePay.find(".online-pay-click").removeClass("pay-current");
                                            $(this).addClass("pay-current");
                                        });

                                        $btnPay.click(function () {
                                            const amount = $balancePay.find(".store-recharge-amount").val();
                                            if (amount < 10) {
                                                layer.msg("最低10元起充");
                                            }

                                            util.post("/admin/store/recharge", {pay_id: payId, amount: amount}, res => {
                                                window.location.href = res.data.pay_url;
                                            });
                                        });
                                    }
                                });
                            }
                        }
                    ]
                }
            ],
            maxmin: false,
            shadeClose: true,
            assign: {}
        });
    }


    function handleUpdate(isUpdate) {
        component.popup({
            submit: isUpdate ? () => {
                const keydown = function (event) {
                    if (event.key === 'F5') {
                        event.preventDefault(); // 阻止默认行为，即刷新
                        layer.msg("正在进行重要更新，请勿刷新网页！");
                    }
                };

                const beforeunload = function (event) {
                    const confirmationMessage = '您正在进行重要更新，请勿随意关闭网页！';
                    event.returnValue = confirmationMessage;
                    return confirmationMessage;
                }

                //开始升级
                util.post({
                    url: "/admin/version/update",
                    loader: false,
                    done: () => {
                        message.success("更新成功，正在重启HTTP服务..");
                        $('.update-tips').html("更新已完成，正在重启HTTP服务..");

                        function stateCheck() {
                            util.timer(() => {
                                return new Promise(resolve => {
                                    util.post({
                                        url: "/admin/system/state",
                                        loader: false,
                                        done: () => {
                                            message.success("重启完成");
                                            setTimeout(() => {
                                                document.removeEventListener("keydown", keydown);
                                                window.removeEventListener("beforeunload", beforeunload);
                                                window.location.reload();
                                            }, 1000);
                                            resolve(false);
                                        },
                                        error: () => {
                                            resolve(true);
                                        },
                                        fail: () => {
                                            resolve(true);
                                        }
                                    });
                                });
                            }, 1000, true);
                        }

                        util.post({
                            url: "/admin/system/restart",
                            loader: false,
                            done: stateCheck,
                            error: stateCheck,
                            fail: stateCheck
                        });
                    }
                });

                layer.closeAll();
                document.addEventListener('keydown', keydown);
                window.addEventListener('beforeunload', beforeunload);

                component.popup({
                    tab: [
                        {
                            name: util.icon("icon-version") + " <span style='color: #63bfea' class='update-tips'>系统正在更新..</span>",
                            form: [
                                {
                                    name: "logs",
                                    type: "textarea",
                                    placeholder: "暂无日志",
                                    default: "正在进行准备工作，请勿刷新或关闭网页..",
                                    height: "662px",
                                    disabled: true,
                                    complete: (popup, val, dom) => {
                                        dom
                                            .css("padding", "15px")
                                            .css("color", "#06b20f")
                                            .css("background-color", "transparent")
                                            .css('overflow', 'hidden')
                                            .css('overflow-y', 'auto')
                                            .css('scrollbar-width', 'none')
                                            .css('border-radius', '0')
                                            .css('resize', 'none')
                                            .css('border', 'none');
                                        dom.parent().parent().parent().parent().css("padding", "0px").parent().css("overflow", "hidden").css("background", "#fff").addClass("update-window");
                                        //dom.get(0).style.setProperty("border-radius", "20px", "important");
                                        //dom.get(0).style.setProperty("border-bottom-right-radius", "20px", "important");
                                        $('.layui-layer-shade').addClass("update-window-shadow");
                                        $('.component-popup').css("background", "#ffffffb0");
                                        $('.layui-layer-title').css("background", "#ffffffb0");
                                        let hash = "";

                                        util.timer(() => {
                                            return new Promise(resolve => {
                                                util.post({
                                                    url: "/admin/version/updateLog?hash=" + hash,
                                                    loader: false,
                                                    done: res => {
                                                        hash = res.data.hash;
                                                        popup.setTextarea("logs", res.data.log);
                                                        dom.scrollTop(dom.prop("scrollHeight"));
                                                        resolve(true);
                                                    },
                                                    error: () => {
                                                        resolve(true);
                                                    },
                                                    fail: () => {
                                                        setTimeout(() => {
                                                            resolve(true);
                                                        }, 1000);
                                                    }
                                                });
                                            });
                                        }, 1, true);
                                    }
                                }
                            ]
                        }
                    ],
                    width: "1080px",
                    height: "720px",
                    maxmin: false,
                    closeBtn: false,
                    shade: 1
                });
            } : false,
            confirmText: util.icon("icon-update") + " 立即更新",
            width: "620px",
            height: "720px",
            tab: [
                {
                    name: util.icon("icon-version") + " <span style='color: #63bfea'>版本列表</span>",
                    form: [
                        {
                            title: false,
                            name: "custom",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(`<div class="layui-timeline version-list"></div>`);

                                const $versionList = dom.find(".version-list");

                                util.post({
                                    url: "/admin/version/list", done: res => {
                                        res.data.forEach(item => {
                                            $versionList.append(`<div class="layui-timeline-item">
                                                                        <i class="layui-icon layui-timeline-axis"></i>
                                                                        <div class="layui-timeline-content">
                                                                          <h3 class="layui-timeline-title fs-5" style="color: ${item.type == "beta" ? "#ff0000" : "#3ebe84"};">${item.version}${item.type == "beta" ? "-beta" : ""} ${item.version == version ? "←" : ''}</h3>
                                                                          <p>${item.content}</p>
                                                                          <p style="margin-top: 10px;color: #867d00;font-size: 12px;">source: <a class="text-primary" href="${item.url}" target="_blank">${item.version}.zip</a> <span class="text-danger">(hash: ${item.hash})</span></p>
                                                                          <p class="fw-normal" style="font-size: 12px;color: #009a25;">${item.create_time}</p>
                                                                        </div>
                                                                      </div>`);
                                        });
                                    }
                                });

                            }
                        }
                    ]
                }
            ],
            maxmin: false,
            shadeClose: true,
            assign: {}
        });
    }

    function handLodLatest() {
        util.post({
            url: "/admin/version/latest",
            loader: false,
            done: res => {
                const $latestCheck = $('.latest-check');
                const type = res.data.type;
                if (version != res.data.version) {
                    $latestCheck.removeClass("bg-primary").addClass("bg-danger").html(type.charAt(0).toUpperCase() + type.slice(1) + " (有新版本)");
                    if (localStorage.getItem("_app_version") != version || res.data.force === true) {
                        handleUpdate(version != res.data.version);
                        localStorage.setItem("_app_version", version);
                    }
                } else {
                    $latestCheck.removeClass("bg-primary").addClass("bg-success").html(type.charAt(0).toUpperCase() + type.slice(1));
                }

                $latestCheck.click(() => {
                    handleUpdate(version != res.data.version);
                });
            }
        });
    }

    function handLoadStoreUser() {
        const $storeUser = $(".store-user");

        util.post({
            url: "/admin/store/personal/info",
            loader: false,
            done: res => {

                /* <img class="d-sm-inline-block  rounded-circle me-1" src="${res.data.avatar}" style="width: 21px;">*/

                $storeUser.append(`<button class="btn btn-sm btn-outline-dark me-1 d-none d-sm-inline-block store-username"><div class="d-flex align-items-center">
                        <span class="fw-semibold">${res.data.username}(<span class="text-warning store-user-balance">￥${res.data.balance}</span>)</span>
                    </div></button>`);

                if (res.data.group) {
                    $storeUser.append(`<a class="btn btn-sm btn-primary me-1 store-group d-sm-inline-block" style="cursor:pointer;"><div class="d-flex align-items-center">
                        <img class="rounded-circle me-1" src="${res.data.group.icon}" style="width: 21px;">
                        <span class="fw-semibold">${res.data.group.name}</span>
                    </div></a>`);

                    let storeGroupTipsIndex = 0;

                    $storeUser.find(".store-group").hover(function () {
                        const msg = `<span class="text-success fw-bold">订阅到期还剩${format.expireTime(res?.data?.group?.expire_time)}</span>\n${res?.data?.group?.power}`.trim().replaceAll("\n", "<br>");
                        storeGroupTipsIndex = layer.tips(msg, this, {
                            tips: [1, '#501536'],
                            time: 0
                        });
                    }, function () {
                        layer.close(storeGroupTipsIndex);
                    });
                }

                if (res?.data?.is_developer === true) {
                    //开发者
                    $storeUser.append(` <a class="btn btn-sm btn-outline-primary fw-bold d-none d-sm-inline-block" href="/admin/store/developer">
                   ${util.icon("icon-kaifazhe")} ${i18n('开发者中心')}
                </a>`);
                }


                $(`.store-username`).click(() => {
                    topUp();
                });

                if (res?.data?.expire_product > 0) {
                    layer.tips(`<span style="color: #e6be2f;">您有${res?.data?.expire_product}个产品将在三天内过期，请尽快进行续费，以确保业务的持续正常运作。</span>`, $(`.store-button`), {
                        tips: 3,
                        time: 5000,
                        tipsMore: true
                    });
                }
            },
            error: () => false,
            fail: () => false
        });
    }


    function handleSyncRemoteItems() {
        const token = util.generateRandStr(16), tokenKey = "_admin_sync_item_token";
        localStorage.setItem(tokenKey, token);
        util.debug("远程商品同步->启动..", "#98FB98");


        util.timer(() => {
            return new Promise(resolve => {
                if (token != localStorage.getItem(tokenKey)) {
                    util.debug("远程商品同步->检测到其他窗口任务启动，已自动关闭当前任务进程..", "#00BFFF");
                    resolve(false);
                    return;
                }
                util.debug("远程商品同步->开始同步..", "#FF8C00");
                util.post({
                    url: "/admin/plugin/ship/remote/items",
                    loader: false,
                    done: res => {
                        if (res?.data?.length > 0) {
                            util.debug(`远程商品同步->需要同步商品数量：${res?.data?.length}`, "#2fdf38");
                            util.timer(() => {
                                return new Promise(call => {
                                    const id = res?.data?.shift();
                                    if (id) {
                                        util.debug(`远程商品同步->开始同步ID：${id}`, "#ed27e9");
                                        util.post({
                                            url: "/admin/plugin/ship/remote/sync",
                                            data: {id: id},
                                            loader: false,
                                            done: res => {
                                                util.debug(`远程商品同步->[${id}]同步结束`, "#2fdf38");
                                                call(true);
                                            },
                                            error: err => {
                                                util.debug(`远程商品同步->[${id}]同步失败`, "#FF5733");
                                                call(true);
                                            },
                                            fail: () => {
                                                util.debug(`远程商品同步->[${id}]网络错误，正在重连..`, "#FF5733");
                                                call(true);
                                            }
                                        });
                                        return;
                                    }
                                    resolve(true);
                                    call(false);
                                })
                            }, 1, true);
                        } else {
                            util.debug(`远程商品同步->持续检测中..`, "#2fdf38");
                            resolve(true);
                        }
                    },
                    error: err => {
                        util.debug(`远程商品同步->出错:${err.msg}`, "#FF5733");
                        resolve(true);
                    },
                    fail: () => {
                        util.debug(`远程商品同步->网络错误，正在重连..`, "#FF5733");
                        resolve(true);
                    }
                });
            });
        }, 10000, true);
    }


    function handlePingStoreNode() {
        const company = [
            `阿里云(<span class="text-success">[delay]ms</span>)`,
            `CloudFlare(<span class="text-success">[delay]ms</span>)`,
            `腾讯云(<span class="text-success">[delay]ms</span>)`
        ];

        util.post({
            url: "/admin/store/node/ping",
            loader: false,
            fail: () => false,
            error: () => false,
            done: res => {
                res?.data?.forEach((ping, index) => {
                    const result = `${ping > 0 ? company[index].replace("[delay]", ping) : "<span class='text-danger'>异常</span>"}`;
                    $(`.store-node-${index}`).html(result).click(() => {
                        util.post("/admin/store/node/save", {
                            index: index
                        }, res => {
                            window.location.reload();
                        });
                    });
                    if (index == res?.index) {
                        $(`.store-node-select`).html("节点:" + result).show();
                    }
                });
            }
        });
    }

    //获取最新版本
    handLodLatest();

    //自动收货任务
    handleAutoReceipt();

    //修改密码和头像
    handPasswordEdit();


    //获取应用商店用户信息
    handLoadStoreUser();

    //同步远程商品
    handleSyncRemoteItems();

    //ping应用商店节点
    util.isPc() && handlePingStoreNode();
}();