!function () {

    const transfer = (title, user = {}) => {
        component.popup({
            submit: '/user/user/transfer',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "划款对象",
                            name: "username",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(format.user(user));
                            }
                        },
                        {
                            title: "划款金额",
                            name: "amount",
                            type: "input",
                            placeholder: "请输入要划款的金额",
                            default: '1.00'
                        }
                    ]
                }
            ],
            confirmText: util.icon('icon-jinduquerentubiao') + " 确认划款",
            assign: user,
            autoPosition: true,
            height: "auto",
            width: "420px",
            maxmin: false,
            done: () => {
                table.refresh();
            }
        });
    }

    const table = new Table("/user/user/get", "#user-table");
    table.setColumns([
        {field: 'id', title: 'ID'},
        {
            field: 'username', title: '会员名', formatter: (id, item) => {
                return format.user(item);
            }
        },
        {field: 'email', title: '邮箱'},
        {field: 'integral', title: '积分', sort: true},
        {
            field: 'balance', title: '余额', sort: true, formatter: balance => {
                return format.money(balance, "#0cbe7e");
            }
        },
        {
            field: 'withdraw_amount', title: '可提现', formatter: balance => {
                return format.money(balance, "#2086ed");
            }
        },
        {
            field: 'level', title: '会员等级', formatter: format.group
        },
        {field: 'note', title: '备注', type: 'input', width: 120},
        {field: 'status', title: '状态', dict: 'user_status'},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-qianbao',
                    class: 'acg-badge-h-dodgerblue',
                    tips: '划款',
                    click: (event, value, row, index) => {
                        transfer(util.icon("icon-qianbao") + "<space></space> 划款", row);
                    }
                }
            ]
        },
    ]);
    table.setSearch([
        {
            title: "ID",
            name: "equal-id",
            type: "input"
        },
        {
            title: "会员名",
            name: "equal-username",
            type: "input"
        },
        {
            title: "邮箱",
            name: "equal-email",
            type: "input"
        }
    ]);
    table.setState("level_id", "level");
    table.setFloatMessage([
        {field: 'lifetime.total_consumption_amount', title: '总消费'},
        {field: 'lifetime.total_recharge_amount', title: '总充值'},
        {field: 'lifetime.total_referral_count', title: '总推广人数'},
        {field: 'lifetime.favorite_item.name', title: '最喜欢的商品'},
        {field: 'lifetime.favorite_item_count', title: '⌞总买次数'},
        {field: 'lifetime.share_item.name', title: '正在推的品'},
        {field: 'lifetime.share_item_count', title: '⌞总推次数'},
        {field: 'lifetime.total_login_count', title: '累计登录次数'},
        {field: 'lifetime.total_profit_amount', title: '总盈利金额'},
        {field: 'lifetime.total_withdraw_amount', title: '总提现金额'},
        {field: 'lifetime.total_withdraw_count', title: '总提现次数'},
        {field: 'lifetime.last_consumption_time', title: '最后消费时间'},
        {field: 'lifetime.last_login_time', title: '最后登录时间'},
        {field: 'lifetime.register_time', title: '账号注册时间'}
    ]);
    table.setDetail([
        {
            field: 'level_id', title: '修改会员等级', formatter: (val, row) => {
                const unique = util.generateRandStr(16);
                _Dict.advanced("level", dict => {
                    dict.forEach(s => {
                        $(`.${unique}`).append(`<option value="${s.id}" ${val == s.id ? 'selected' : ''}>${s.name}</option>`);
                    });
                    $(`.${unique}`).change(function () {
                        const levelId = this.value;
                        util.post("/user/user/changeLevel", {user_id: row.id, level_id: levelId}, () => {
                            message.success(`「${row.username}」的会员等级已变更`);
                        });
                    });
                });
                return `<select class="${unique}"  lay-filter="${unique}"></select>`;
            }
        }
    ]);
    table.onResponse(data => {
        $('.data-count .user-count').html(data.user_count);
        $('.data-count .user-total-balance').html(data.user_total_balance);
    });
    table.render();
}();