!function () {
    let table = null, billTable = null, defaultWithdrawAmount = 0, billType = [
        {id: 0, name: "购买应用"},
        {id: 1, name: "续费应用"},
        {id: 2, name: "充值"},
        {id: 3, name: "人工操作"},
        {id: 4, name: "渠道分红"},
        {id: 6, name: "子站分红"},
        {id: 5, name: "应用分红"},
        {id: 11, name: "提现"},
        {id: 12, name: "提现驳回"}
    ];

    const WithdrawalApply = (title, assign = {}) => {
        component.popup({
            submit: '/admin/store/withdrawal/apply',
            autoPosition: true,
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "支付宝账号",
                            name: "username",
                            type: "input",
                            placeholder: "请输入收款支付宝账号",
                            required: true,
                            tips: "请确保所填写的支付宝账号与实名认证信息保持一致，如无法通过名称验证，相关资金将被冻结且无法解冻。"
                        },
                        {
                            title: "兑现金额",
                            name: "amount",
                            type: "number",
                            placeholder: "请填写兑现金额，最低100元起",
                            tips: "兑现最低100元起",
                            required: true,
                            default: defaultWithdrawAmount > 100 ? defaultWithdrawAmount : 100
                        }
                    ]
                }
            ],
            assign: assign,
            width: "500px",
            maxmin: false,
            confirmText: `${util.icon("icon-jinduquerentubiao")} 确认提交`,
            done: () => {
                getUserWithdrawBalance();
                table.refresh();
            }
        });
    }


    const getUserWithdrawBalance = () => {
        util.post({
            url: "/admin/store/personal/info",
            loader: false,
            done: res => {
                defaultWithdrawAmount = res?.data?.withdraw_amount;
                $(`.withdrawal-apply-text`).html(`兑现(￥${res?.data?.withdraw_amount})`);
            },
            error: () => {
            },
            fail: () => {

            }
        });
    }

    const identityCheck = (call) => {
        util.post({
            url: "/admin/store/identity/status",
            done: res => {
                if (!res.data.status) {
                    if (res?.data?.url) {
                        $('.cert-qrcode').qrcode({
                            render: "canvas",
                            width: 200,
                            height: 200,
                            text: res?.data?.url
                        });
                        $('.cert-status-scan').show(200);


                        util.timer(() => {
                            return new Promise(resolve => {
                                util.post({
                                    url: "/admin/store/identity/status?tradeNo=" + res.data.tradeNo,
                                    loader: false,
                                    done: result => {
                                        if (result.data.status) {
                                            window.location.reload();
                                            resolve(false);
                                        } else {
                                            resolve(true);
                                        }
                                    },
                                    fail: () => resolve(true),
                                    error: () => resolve(true)
                                });
                            });
                        }, 3000, true);

                    } else {
                        $('.cert-button').click(() => {
                            util.post("/admin/store/identity/certification", {
                                cert_name: $("#cert-name").val(),
                                cert_no: $("#cert-no").val()
                            }, response => {
                                window.location.reload();
                            });
                        });
                        $('.cert-status-submit').show(200);
                    }

                    $('.identity-block').show(200);
                } else {
                    $('.trade-block').show(100);
                    call();
                }
            }
        });
    }


    identityCheck(() => {
        getUserWithdrawBalance();

        table = new Table("/admin/store/withdrawal/get", "#withdrawal-table");
        table.setPagination(10, [10, 20, 50, 100]);
        table.setColumns([
            {
                field: 'trade_no', title: '流水号'
            },
            {
                field: 'amount', title: '兑现金额', formatter: amount => {
                    return format.color(`￥${amount}`, "#2bb452");
                }
            },
            {field: 'alipay_account', title: '支付宝账号'},
            {
                field: 'status', title: '进度', dict: [
                    {id: 0, name: format.color("系统处理中", "red")},
                    {id: 1, name: format.color("已到账", "green")},
                    {id: 2, name: format.color("已驳回", "grey")}
                ]
            },
            {field: 'create_time', title: '申请时间'},
            {field: 'handle_time', title: '处理时间'},
            {field: 'handle_message', title: '更多信息'},
        ]);

        table.setSearch([
            {title: "流水号", name: "equal-trade_no", type: "input"},
            {title: "支付宝账号", name: "equal-alipay_account", type: "input"},
            {title: "申请时间", name: "between-create_time", type: "date"},
        ]);
        table.setState("status", [
            {id: 0, name: '处理中'},
            {id: 1, name: '已到账'},
            {id: 2, name: '已驳回'}
        ]);
        table.render();
        $('.withdrawal-apply').click(() => {
            WithdrawalApply(util.icon("icon-tixian") + " 申请兑现");
        });


        table = new Table("/admin/store/bill/get", "#bill-table");
        table.setPagination(10, [10, 20, 50, 100]);
        table.setColumns([
            {field: 'type', title: '账单类型', dict: billType},
            {
                field: 'amount', title: '金额', formatter: (amount, item) => {
                    const strikethrough = item.status == 2 ? 'strikethrough' : '';

                    if (item.action == 1) {
                        return format.success("+" + amount, strikethrough);
                    } else {
                        return format.danger("-" + amount, strikethrough);
                    }
                }
            },
            {
                field: 'before_balance', title: '变更前余额', formatter: (balance, item) => {
                    if (item.status != 0) {
                        return "-";
                    }
                    return format.color("￥" + balance, "#b09d9d");
                }
            },
            {
                field: 'after_balance', title: '变更后余额', formatter: (balance, item) => {
                    if (item.status != 0) {
                        return "-";
                    }
                    return format.color("￥" + balance, "#7ac1ff");
                }
            },
            {field: 'status', title: '状态', dict: 'user_bill_status'},
            {
                field: 'unfreeze_time', title: '解冻时间', formatter: (time, item) => {
                    if (item.status == 1) {
                        return time;
                    }
                    return '-';
                }
            },
            {field: 'is_withdraw', title: '可提现', dict: 'user_bill_is_withdraw'},
            {field: 'trade_no', title: '关联订单号'},
            {field: 'remark', title: '账单备注'},
            {field: 'update_time', title: '变更时间'},
            {field: 'create_time', title: '创建时间'}
        ]);
        table.setSearch([
            {
                title: "关联订单号",
                name: "equal-trade_no",
                type: "input"
            },
            {
                title: "账单状态",
                name: "equal-status",
                type: "select",
                dict: "user_bill_status"
            },
            {
                title: "创建时间",
                name: "between-create_time",
                type: "date"
            }
        ]);
        table.setState("type", billType);
        table.render();
    });
}();