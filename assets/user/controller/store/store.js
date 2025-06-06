!function () {
    let table = null, subscriptionTimes = [30, 90, 180, 360, 35640], userBalance = 0;

    const openTitle = (icon, title) => {
        return `<div class="common-item"><img src="${icon}" class="item-icon" style="width: 20px;height: 20px;"> <div class="item-name" style="font-size: 1rem;">${title}</div></div>`;
    }

    const openPayment = (amount, callback) => {
        let isBalance = true, payId = 0, openPaymentIndex = 0;
        component.popup({
            submit: false,
            confirmText: "付款",
            autoPosition: true,
            width: "520px",
            tab: [
                {
                    name: util.icon("icon--yue") + " 付款",
                    form: [
                        {
                            title: false,
                            name: "custom",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(`<div class="pay-container">               
<div class="layout-box">
                    <div class="title">余额支付 <span class="text-success wallet-balance">0.00</span></div>
                    <div class="pay-list balance-pay"></div>
                </div>

<div class="layout-box">
                    <div class="title">在线支付</div>
                    <div class="pay-list online-pay"></div>
                </div>
                <div class="layout-box">
                    <button type="button" class="btn-pay">确认付款（￥${amount}）</button>
                </div>
</div>`);
                                const $onlinePay = dom.find(".online-pay");
                                const $balancePay = dom.find(".balance-pay");
                                const $walletBalance = dom.find(".wallet-balance");
                                const $btnPay = dom.find(".btn-pay");
                                util.post({
                                    url: "/user/store/pay/list", done: res => {
                                        $walletBalance.html("￥" + res.balance);

                                        $balancePay.append(`<div class="pay-item pay-current wallet-balance-click"><img src="/assets/common/images/balance.png"><span>我的钱包</span></div>`);
                                        res.data.forEach(item => {
                                            $onlinePay.append(`<div data-payId="${item.id}" class="pay-item online-pay-click"><img src="${item.icon}"><span>${item.name}</span></div>`);
                                        });

                                        function checkCombination() {
                                            if (isBalance && payId > 0) {
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

                                        checkCombination();

                                        $('.wallet-balance-click').click(function () {
                                            isBalance = !isBalance;
                                            if (isBalance) {
                                                $(this).addClass("pay-current");
                                            } else {
                                                $(this).removeClass("pay-current");
                                            }

                                            checkCombination();
                                        });

                                        $('.online-pay-click').click(function () {
                                            const id = $(this).attr("data-payId");
                                            $(".online-pay-click").removeClass("pay-current");

                                            if (id != payId) {
                                                payId = $(this).attr("data-payId");
                                                $(this).addClass("pay-current");
                                            } else {
                                                payId = 0;
                                            }

                                            checkCombination();
                                        });

                                        $btnPay.click(function () {
                                            typeof callback === "function" && callback(isBalance, payId, openPaymentIndex);
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
            assign: {},
            renderComplete: (unique, index) => {
                openPaymentIndex = index;
            }
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
                    name: util.icon("icon--yue") + " 钱包充值",
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
                                    url: "/user/store/pay/list", done: res => {
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

                                            util.post("/user/store/recharge", {pay_id: payId, amount: amount}, res => {
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


    const billModal = (item = {}, subscriptionId = 0, success = null) => {
        let billModalIndex = 0;

        component.popup({
            submit: false,
            tab: [
                {
                    name: openTitle(item.icon, item.name),
                    form: [
                        {
                            title: false,
                            name: "introduce_" + item.name,
                            type: "custom",
                            complete: (form, dom) => {
                                let payList = '', selectedSubscription, selectAmount;
                                _Dict.advanced("store_subscription", data => {
                                    let add = 0;
                                    data.forEach((s, index) => {
                                        if (item[s.id] > 0) {
                                            if (add == 0) {
                                                selectAmount = item[s.id];
                                                selectedSubscription = index;
                                            }
                                            const dayAmount = (new Decimal(item[s.id])).div(subscriptionTimes[index]).getAmount(2);
                                            payList += `<div class="subscription-item ${add == 0 ? 'subscription-current' : ''}" data-subscription="${index}" data-amount="${item[s.id]}"><span style="color: #496b93ab;"><span style="color: #D38200;font-size: 18px;font-weight: bold;">￥${item[s.id]}</span>/${s.name}</span><span style="color: #BDB8B8;font-size: 13px;text-decoration:line-through;">原价:${item[s.id] * 2}</span><span style="color: #D38200;font-size:12px;">${index == 4 ? '终身可用' : `低至${dayAmount}元/天`}</span></div>`;
                                            add++;
                                        }
                                    });
                                });
                                dom.html(`<div>     

<div class="alert alert-success" role="alert">
                    <p class="mb-0">
                      您所购买的插件或个人版等产品，将统一归属于您的应用商店账户名下。无论您更换服务器或重新安装程序，只需登录购买时所使用的应用商店账户，即可迅速将产品绑定至新的网站上。
                    </p>
                  </div>          
            
                    <div class="mb-3 store-introduce">
                      ${i18n(item.introduce)}
                    </div>
                    
                    <div class="subscription-container">
                    <div class="layout-box">
                    <div class="title">订阅类型</div>
                            <div class="subscription-list online-pay">${payList}</div>
                        </div>
                    </div>
         
        
                <form class="form-store-login">
                  <div class="row g-sm mb-4">
                      <button type="button" class="btn fw-bold btn-lg btn-alt-primary py-2 text-primary btn-purchasing">
                        ${i18n('立即付款')}（￥${selectAmount}）
                      </button>
                  </div>
                </form>
              </div>`);
                                const $onlinePay = dom.find(".online-pay");
                                const $purchasing = dom.find(`.btn-purchasing`);

                                $onlinePay.find(".subscription-item").click(function () {
                                    $onlinePay.find(".subscription-item").removeClass("subscription-current");
                                    $(this).addClass("subscription-current");
                                    selectAmount = $(this).attr("data-amount");
                                    selectedSubscription = $(this).attr("data-subscription");
                                    $purchasing.html(`立即付款（￥${selectAmount}）`);
                                });

                                $purchasing.click(() => {
                                    openPayment(selectAmount, (isBalance, payId, openPaymentIndex) => {
                                        util.post("/user/store/purchase", {
                                            type: item?.is_group === true ? 1 : 0,
                                            item_id: item.id,
                                            subscription: selectedSubscription,
                                            pay_id: payId,
                                            balance: isBalance ? 1 : 0,
                                            subscription_id: subscriptionId
                                        }, res => {
                                            if (res.data.status == 2) {
                                                layer.close(openPaymentIndex);
                                                layer.close(billModalIndex);
                                                layer.msg("付款成功");
                                                typeof success == "function" && success();
                                                updateBalance();
                                            } else {
                                               window.location.href = res.data.pay_url;
                                            }
                                        });
                                    });
                                });
                            }
                        }
                    ]
                }
            ],
            maxmin: false,
            autoPosition: true,
            width: "876px",
            renderComplete: (unique, index) => {
                billModalIndex = index;
                $(`.${unique} .layui-layer-title span`)
                    .css("padding", "0 10px")
                    .each(function () {
                        if (util.isMobile()) {
                            this.style.setProperty('width', '87px', 'important');
                            this.style.setProperty('max-width', '87px', 'important');
                            this.style.setProperty('min-width', '87px', 'important');
                        }
                    });
                $(`.${unique} .layui-layer-title span:first`).css("margin-left", "20px");
                $(`.${unique} .layui-card-body`).css("padding-top", "10px");
            }
        });
    }

    const updateBalance = () => {
        util.post({
            url: "/user/store/personal/info",
            loader: false,
            done: res => {
                userBalance = res?.data?.balance;
                $('.store-user-balance').html(`￥${userBalance}`);
            }
        });
    }

    const storePowers = () => {
        component.popup({
            submit: false,
            tab: [
                {
                    name: util.icon("icon-plugin-general") + "<space></space>我的订阅",
                    form: [
                        {
                            name: "subscription",
                            type: "custom",
                            complete: (popup, dom) => {

                                dom.html(`<div class="block block-rounded">
        <div class="block-header block-header-default">
            <button type="button" class="btn btn-outline-primary btn-sm wallet-recharge wap-mb1">${util.icon("icon-zhifu")}<space></space>${i18n("钱包充值")}</button>
            <button type="button" class="btn btn-outline-success btn-sm renewal-subscription wap-mb1">${util.icon("icon-update")}<space></space>${i18n("一键续费")}</button>
            <button type="button" class="btn btn-outline-info btn-sm bind-subscription wap-mb1">${util.icon("icon-mimashezhi-xiugaimima")}<space></space>${i18n("授权更换至本机")}</button>
        </div>
        <div class="block-content pt-0">
            <table id="store-subscription-table"></table>
        </div>
    </div>`);

                                const subscriptionTable = new Table(
                                    "/user/store/powers",
                                    dom.find('#store-subscription-table')
                                );
                                subscriptionTable.disablePagination();
                                subscriptionTable.setColumns([
                                    {checkbox: true},
                                    {
                                        field: 'name', title: '订阅项目', class: "nowrap", formatter: (name, item) => {
                                            return format.plugin(item);
                                        }
                                    },
                                    {
                                        field: 'subscription',
                                        title: '订阅方式',
                                        class: "nowrap",
                                        dict: "store_renewal_subscription"
                                    },
                                    {
                                        field: 'auto_subscription',
                                        title: '自动续费',
                                        class: "nowrap",
                                        type: "switch",
                                        text: "开启|关闭",
                                        change: (value, item) => {
                                            util.post("/user/store/power/renewal/auto", {
                                                item_id: item.id,
                                                type: item.is_group ? 1 : 0
                                            }, res => {
                                                if (value == 1) {
                                                    layer.msg("已启用自动续费");
                                                } else {
                                                    layer.msg("已关闭自动续费");
                                                }
                                            });
                                        }
                                    },
                                    {
                                        field: 'price', title: '续订价格', class: "nowrap", formatter: price => {
                                            return `<b class="text-warning">￥${format.amounts(price)}</b>`
                                        }, align: "center"
                                    },
                                    {
                                        field: 'expire_time', title: '到期时间', class: "nowrap", formatter: time => {
                                            const expireTime = format.expireTime(time);
                                            if (expireTime === false) {
                                                return `<span class="text-danger">已到期</span>`;
                                            }
                                            return `<span class="text-success">还剩${expireTime}</span>`;
                                        }
                                    },
                                    {field: 'create_time', class: "nowrap", title: '开始时间'},
                                    {field: 'server_ip', class: "nowrap", title: '授权IP'},
                                    {
                                        field: 'hwid', class: "nowrap", title: '授权设备', formatter: (val, item) => {
                                            if (item.is_local_machine) {
                                                return format.success("本机");
                                            }
                                            return val;
                                        }
                                    },
                                    {
                                        field: 'message', title: '', formatter: (message, item) => {
                                            if (message) {
                                                return `<span class="subscription-message-${item.id}">${message}</span>`;
                                            }

                                            return `<span class="subscription-message-${item.id}"></span>`;
                                        }
                                    },
                                    {
                                        field: 'operation', title: '', type: 'button', class: "nowrap", buttons: [
                                            {
                                                class: 'btn-outline-success',
                                                title: "更换套餐",
                                                icon: "icon-change",
                                                click: (event, value, row, index) => {
                                                    util.post("/user/store/power/detail", {
                                                        item_id: row.power_id,
                                                        is_group: row.is_group ? 1 : 0
                                                    }, res => {
                                                        billModal(res.data, row.id, () => {
                                                            subscriptionTable.refresh();
                                                        });
                                                    });
                                                },
                                                show: item => item.subscription != 4
                                            }
                                        ]
                                    }
                                ]);

                                subscriptionTable.render();

                                dom.find('.wallet-recharge').click(() => {
                                    topUp();
                                });

                                dom.find('.renewal-subscription').click(() => {
                                    const selections = subscriptionTable.getSelections();
                                    if (selections.length == 0) {
                                        layer.msg("请选择要续费的订阅项目");
                                        return;
                                    }

                                    let amount = new Decimal(0);
                                    let powers = [];

                                    selections.forEach(item => {
                                        amount = amount.add(item.price);
                                        powers.push(`<b class="text-success">${item?.is_group ? "用户组" : "插件"}-${util.plainText(item.name)}</b>`);
                                    });

                                    if (parseFloat(userBalance) < parseFloat(amount.getAmount())) {
                                        layer.msg("钱包余额不足，无法进行快速续订。");
                                        return;
                                    }

                                    message.ask(`<div style="text-align: left;"><p>续订项目：${powers.join("、")}</p><p>支付金额：<b class="text-warning">￥${amount.getAmount()}</b></p><p class="mt-3 fs-sm text-danger">请保证钱包有足够的余额，续订会通过余额进行扣款。</p></div>`, () => {
                                        let index = 0;
                                        let loaderIndex = layer.load(2, {shade: ['0.3', '#fff']});
                                        util.timer(() => {
                                            return new Promise(resolve => {
                                                const item = selections[index];
                                                if (item) {
                                                    const $message = $(`.subscription-message-${item.id}`);
                                                    $message.html(`<span class="text-primary">正在订阅..</span>`);
                                                    util.post({
                                                        url: "/user/store/power/renewal",
                                                        loader: false,
                                                        data: {
                                                            type: item?.is_group ? 1 : 0,
                                                            item_id: item.id,
                                                            subscription: item.subscription
                                                        },
                                                        done: result => {
                                                            if (result?.data?.status === true) {
                                                                $message.html(`<span class="text-success">订阅完成</span>`);
                                                            } else {
                                                                $message.html(`<span class="text-danger">订阅失败</span>`);
                                                            }
                                                        },
                                                        error: () => {
                                                            $message.html(`<span class="text-danger">订阅失败</span>`);
                                                        },
                                                        fail: () => {
                                                            $message.html(`<span class="text-danger">订阅失败</span>`);
                                                        }
                                                    });
                                                    index++;
                                                    resolve(true);
                                                    return;
                                                } else if (index > 0) {
                                                    subscriptionTable.refresh();
                                                    updateBalance();
                                                }
                                                layer.close(loaderIndex);
                                                resolve(false);
                                            });
                                        }, 1000, true);
                                    }, "请确认您续费的订阅！");
                                });


                                dom.find('.bind-subscription').click(() => {
                                    const selections = subscriptionTable.getSelections();
                                    if (selections.length == 0) {
                                        layer.msg("请选择要授权到本机的订阅");
                                        return;
                                    }

                                    let powers = [];

                                    selections.forEach(item => {
                                        powers.push(`<b class="text-success">${item?.is_group ? "用户组" : "插件"}-${util.plainText(item.name)}</b>`);
                                    });


                                    message.ask(`<p>${powers.join("、")}</p><p class="mt-3 fs-sm text-danger">将授权转移至本机后，其他机器上的插件将被停用。如果本机已存在授权的插件，则授权转移将失败。</p>`, () => {
                                        let index = 0;
                                        let loaderIndex = layer.load(2, {shade: ['0.3', '#fff']});
                                        util.timer(() => {
                                            return new Promise(resolve => {
                                                const item = selections[index];
                                                if (item) {
                                                    const $message = $(`.subscription-message-${item.id}`);
                                                    $message.html(`<span class="text-primary">授权中..</span>`);
                                                    util.post({
                                                        url: "/user/store/power/renewal/bind",
                                                        loader: false,
                                                        data: {
                                                            type: item?.is_group ? 1 : 0,
                                                            item_id: item.id,
                                                            subscription: item.subscription
                                                        },
                                                        done: result => {
                                                            if (result?.data?.status === true) {
                                                                $message.html(`<span class="text-success">已完成</span>`);
                                                            } else {
                                                                $message.html(`<span class="text-danger">失败</span>`);
                                                            }
                                                        },
                                                        error: () => {
                                                            $message.html(`<span class="text-danger">失败</span>`);
                                                        },
                                                        fail: () => {
                                                            $message.html(`<span class="text-danger">失败</span>`);
                                                        }
                                                    });
                                                    index++;
                                                    resolve(true);
                                                    return;
                                                } else if (index > 0) {
                                                    subscriptionTable.refresh();
                                                }
                                                layer.close(loaderIndex);
                                                resolve(false);
                                            });
                                        }, 1000, true);
                                    }, "请确认您要更换授权的项目！");
                                });
                            }
                        },
                    ]
                }
            ],
            assign: {},
            autoPosition: true,
            width: "1480px"
        });
    }

    $('.open-store-powers').click(() => {
        storePowers();
    });

    util.post({
        url: "/user/store/personal/info",
        done: res => {
            userBalance = res?.data?.balance;

            table = new Table("/user/store/list", "#store-table");
            table.setPagination(12, [12, 20, 50, 100]);
            table.setColumns([
                {
                    field: 'name', class: "nowrap", title: '应用名称', formatter: (name, item) => {
                        return format.plugin(item);
                    }
                },
                {
                    field: 'user.username', class: "nowrap", title: '开发商', formatter: username => {
                        return format.badge(username, "btn-outline-dodgerblue");
                    }
                },
                {field: 'type', class: "nowrap", title: '类型', dict: "store_plugin_type"},
                {
                    field: 'arch', class: "nowrap", title: '支持架构', formatter: arch => {
                        let archs = [format.badge('<i class="fa fa-window-restore opacity-50 me-1"></i>CLI', "acg-badge-h-green"), format.badge('<i class="fa fa-window-maximize opacity-50 me-1"></i>FPM', "acg-badge-h-dodgerblue")];
                        if (arch == 0) {
                            return archs.join("");
                        } else if (arch == 1) {
                            return archs[0];
                        } else if (arch == 2) {
                            return archs[1];
                        }
                        return '-';
                    }
                },
                {field: 'description', title: '描述'},
                {field: 'version', class: "nowrap", title: '版本号'},
                {
                    field: 'monthly_fee', title: '价格', class: "nowrap", formatter: (fee, item) => {
                        if (item.is_free == 1) {
                            return format.success("公益(免费)");
                        }

                        if (item?.authorize?.available === true && item?.authorize?.expire_time != "free") {
                            return format.primary("订阅中");
                        }

                        let amount = "";

                        _Dict.advanced("store_subscription", subs => {
                            for (const subsKey in subs) {
                                let amt = item[subs[subsKey].id] ?? 0;
                                if (parseFloat(amt) > 0) {
                                    amount += format.badge(`￥${format.amounts(amt)}`, "acg-badge-h-red");
                                    break;
                                }
                            }
                        });

                        return amount;
                    }
                },
                {
                    field: 'operation', title: '', class: "nowrap", type: 'button', buttons: [
                        {
                            class: 'btn-outline-success',
                            icon: 'icon-xiazai',
                            title: "安装",
                            click: (event, value, row, index) => {
                                message.ask(`您正在安装插件(${row.name})，是否继续？`, () => {
                                    util.post("/user/store/install", {key: row.key}, res => {
                                        message.success("[" + row.name + "] 已安装!");
                                        table.refresh();
                                    });
                                });
                            },
                            show: item => !item.installed && item?.authorize?.available === true
                        },
                        {
                            class: 'btn-outline-success',
                            icon: 'icon-shouye2',
                            title: "购买",
                            click: (event, value, row, index) => {
                                billModal(row, 0, () => {
                                    table.refresh();
                                });
                            },
                            show: item => item?.authorize?.available === false
                        }
                    ]
                },
            ]);


            table.setSearch([
                {title: "搜索插件", name: "keywords", type: "input"}
            ]);
            table.setState("type", "store_plugin_type");
            table.render();


            $('.add-plugin').click(() => {
                pluginDeveloperModal(util.icon("icon-tianjia") + " 创建插件");
            });
        },
        error: () => {
            component.popup({
                submit: false,
                tab: [
                    {
                        name: '<i class="si si-login"></i> 登录',
                        form: [
                            {
                                title: false,
                                name: "login_page",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`<div class="">               
                  <div class="alert alert-warning d-flex align-items-center" role="alert">
                   
                    <p class="mb-0">
                      访问我们的应用商店需要先登录应用商店账号。应用商店内提供大量插件、模板和主题等资源供您安装。
                    </p>
                  </div>
          
                <form class="form-store-login">
                  <div class="form-floating mb-4">
                             <input type="text" class="form-control" id="login-username" name="username" placeholder="手机号/用户名">
                            <label class="form-label" for="login-username">账号/手机号</label>
                  </div>
                  
                  <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="login-password" name="password" placeholder="请输入密码">
                    <label class="form-label" for="login-password">密码</label>
                  </div>
                  <div class="row mb-4">
                    <div class="col-sm-6 col-6">
                           <div class="form-floating">
                            <input type="text" class="form-control" id="login-captcha" name="captcha" placeholder="请输入验证码">
                            <label class="form-label" for="login-captcha">图形验证码</label>
                          </div>
                    </div>
                    <div class="col-sm-6 col-6">
                           <img src="/user/store/auth/captcha?type=login" style="cursor:pointer;" class="img-captcha-login" onclick="this.src='/user/store/auth/captcha?type=login&rand=' + util.generateRandStr(12);" alt="更换验证码">
                    </div>
                  </div>
                  
                  <div class="row g-sm mb-4">
                      <button type="button" class="btn btn-lg btn-alt-success py-2 text-success btn-login">
                        登入
                      </button>
                  </div>
                </form>
              </div>`);
                                    let tipsIndex = null;
                                    $('#login-username').on('input', function () {
                                        const phone = $(this).val();

                                        if (/^\d{5,}$/.test(phone) && !/^1[3-9]\d{9}$/.test(phone)) {
                                            if (tipsIndex === null) {
                                                tipsIndex = layer.tips("中国大陆手机号直接输入，非大陆手机号需要加国家代码，如香港：+852********", $(this), {
                                                    tips: [1, '#501536'], time: 0
                                                });
                                            }
                                        } else {
                                            layer.close(tipsIndex);
                                            tipsIndex = null;
                                        }
                                    });

                                    $(".btn-login").click(() => {
                                        util.post("/user/store/auth/login", {
                                            username: $("#login-username").val(),
                                            password: $("#login-password").val(),
                                            captcha: $("#login-captcha").val()
                                        }, res => {
                                            message.success("登录成功");
                                            window.location.reload();
                                        }, (res) => {
                                            $('.img-captcha-login').click();
                                            message.error(res.msg);
                                        });
                                    });
                                }
                            }
                        ]
                    },
                    {
                        name: "<i class='fa fa-user-plus'></i> 注册",
                        form: [
                            {
                                title: false,
                                name: "register_page",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`<div><form class="form-store-register">
                  <div class="form-floating mb-4">
                    <input type="text" class="form-control" id="register-username"  placeholder="用户名">
                    <label class="form-label" for="register-username">用户名</label>
                  </div>
           
                   <div class="row mb-4">
                    <div class="col-sm-4 col-4">
                      <div class="form-floating">
                              <select lay-ignore class="form-select" id="register-phone-country"  aria-label="请选择国家"></select>
                              <label class="form-label" for="register-phone-country">国家</label>
                      </div>
                    </div>
                    <div class="col-sm-8 col-8">
                      <div class="form-floating">
                          <input type="number" class="form-control" id="register-phone"  placeholder="手机号">
                          <label class="form-label" for="register-phone">手机号</label>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <div class="col-sm-8 col-8">
                           <div class="form-floating">
                            <input type="text" class="form-control" id="register-code" placeholder="请输入短信验证码">
                            <label class="form-label" for="register-code">短信验证码</label>
                          </div>
                    </div>
                    <div class="col-sm-4 col-4">
                          <button type="button" class="w-100 btn btn-primary py-3 btn-send-register-code">发送验证码</button>
                    </div>
                  </div>
                  <div class="form-floating mb-4">
                    <input type="text" class="form-control" id="register-password"  placeholder="请设置登录密码">
                    <label class="form-label" for="register-password">登录密码</label>
                  </div>
                  <div class="row mb-4">
                    <div class="col-sm-6 col-6">
                           <div class="form-floating">
                            <input type="text" class="form-control" id="register-captcha" placeholder="请输入验证码">
                            <label class="form-label" for="register-captcha">图形验证码</label>
                          </div>
                    </div>
                    <div class="col-sm-6 col-6">
                           <img src="/user/store/auth/captcha?type=register" style="cursor:pointer;" class="img-captcha-register" onclick="this.src='/user/store/auth/captcha?type=register&rand=' + util.generateRandStr(12);" alt="更换验证码">
                    </div>
                  </div>
                  
                  <div class="row g-sm mb-4">
                      <button type="button" class="btn btn-lg btn-alt-success py-2 text-success btn-register">
                        确认注册
                      </button>
                  </div>
                </form>
              </div>`);
                                    const $imageCode = $('.img-captcha-register');
                                    const $registerPhoneCountry = $("#register-phone-country");


                                    $('.btn-send-register-code').click(function () {
                                        let phone = $("#register-phone").val();

                                        if ($registerPhoneCountry.val() !== "86") {
                                            phone = "+" + $registerPhoneCountry.val() + phone;
                                        }

                                        util.post("/user/store/auth/sms/send", {
                                            phone: phone,
                                            type: "register",
                                            captcha: $("#register-captcha").val()
                                        }, res => {
                                            message.success("短信验证码已发送至您的手机，请注意查收");
                                            util.countDown(this, 60);
                                            $imageCode.click();
                                        }, (res) => {
                                            message.error(res.msg);
                                            $imageCode.click();
                                        });
                                    });


                                    $('.btn-register').click(() => {
                                        let phone = $("#register-phone").val();
                                        if ($registerPhoneCountry.val() !== "86") {
                                            phone = "+" + $registerPhoneCountry.val() + phone;
                                        }

                                        util.post("/user/store/auth/register", {
                                            username: $("#register-username").val(),
                                            password: $("#register-password").val(),
                                            phone: phone,
                                            code: $("#register-code").val(),
                                            captcha: $("#register-captcha").val()
                                        }, res => {
                                            message.success("注册成功");
                                            window.location.reload();
                                        }, (res) => {
                                            message.error(res.msg);
                                            $imageCode.click();
                                        });
                                    });


                                    _Dict.advanced("sms_country", data => {
                                        data.forEach(item => {
                                            $registerPhoneCountry.append(`<option value="${item.id}">${item.name}(+${item.id})</option>`);
                                        });
                                    });
                                }
                            }
                        ]
                    },
                    {
                        name: "<i class='fa fa-unlock'></i> 忘记密码",
                        form: [
                            {
                                title: false,
                                name: "reset_page",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.html(`<div><form class="form-store-register">
                   <div class="row mb-4">
                    <div class="col-sm-4 col-4">
                      <div class="form-floating">
                              <select lay-ignore class="form-select" id="reset-phone-country"  aria-label="请选择国家"></select>
                              <label class="form-label" for="reset-phone-country">国家</label>
                      </div>
                    </div>
                    <div class="col-sm-8 col-8">
                      <div class="form-floating">
                          <input type="number" class="form-control" id="reset-phone"  placeholder="手机号">
                          <label class="form-label" for="reset-phone">手机号</label>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-4">
                    <div class="col-sm-8 col-8">
                           <div class="form-floating">
                            <input type="text" class="form-control" id="reset-code" placeholder="请输入短信验证码">
                            <label class="form-label" for="reset-code">短信验证码</label>
                          </div>
                    </div>
                    <div class="col-sm-4 col-4">
                          <button type="button" class="w-100 btn btn-primary py-3 btn-send-reset-code">发送验证码</button>
                    </div>
                  </div>
                  <div class="form-floating mb-4">
                    <input type="text" class="form-control" id="reset-password"  placeholder="请设置登录密码">
                    <label class="form-label" for="reset-password">设置新登录密码</label>
                  </div>
                  <div class="row mb-4">
                    <div class="col-sm-6 col-6">
                           <div class="form-floating">
                            <input type="text" class="form-control" id="reset-captcha" placeholder="请输入验证码">
                            <label class="form-label" for="reset-captcha">图形验证码</label>
                          </div>
                    </div>
                    <div class="col-sm-6 col-6">
                           <img src="/user/store/auth/captcha?type=reset" style="cursor:pointer;" class="img-captcha-reset" onclick="this.src='/user/store/auth/captcha?type=reset&rand=' + util.generateRandStr(12);" alt="更换验证码">
                    </div>
                  </div>
                  
                  <div class="row g-sm mb-4">
                      <button type="button" class="btn btn-lg btn-alt-success py-2 text-success btn-reset">
                        确认重置
                      </button>
                  </div>
                </form>
              </div>`);
                                    const $imageCode = $('.img-captcha-reset');
                                    const $resetPhoneCountry = $("#reset-phone-country");
                                    const $resetCaptcha = $("#reset-captcha");


                                    $('.btn-send-reset-code').click(function () {
                                        let phone = $("#reset-phone").val();

                                        if ($resetPhoneCountry.val() !== "86") {
                                            phone = "+" + $resetPhoneCountry.val() + phone;
                                        }

                                        util.post("/user/store/auth/sms/send", {
                                            phone: phone,
                                            type: "reset",
                                            captcha: $resetCaptcha.val()
                                        }, res => {
                                            message.success("短信验证码已发送至您的手机，请注意查收");
                                            util.countDown(this, 60);
                                            $imageCode.click();
                                        }, (res) => {
                                            message.error(res.msg);
                                            $imageCode.click();
                                        });
                                    });


                                    $('.btn-reset').click(() => {
                                        let phone = $("#reset-phone").val();
                                        if ($resetPhoneCountry.val() !== "86") {
                                            phone = "+" + $resetPhoneCountry.val() + phone;
                                        }

                                        util.post("/user/store/auth/reset", {
                                            password: $("#reset-password").val(),
                                            phone: phone,
                                            code: $("#reset-code").val(),
                                            captcha: $resetCaptcha.val()
                                        }, res => {
                                            message.success("重置成功");
                                            window.location.reload();
                                        }, (res) => {
                                            message.error(res.msg);
                                            $imageCode.click();
                                        });
                                    });

                                    _Dict.advanced("sms_country", data => {
                                        data.forEach(item => {
                                            $resetPhoneCountry.append(`<option value="${item.id}">${item.name}(+${item.id})</option>`);
                                        });
                                    });
                                }
                            }
                        ]
                    },
                ],
                closeBtn: false,
                maxmin: false,
                autoPosition: true,
                width: "520px"
            });
        }
    });
}();