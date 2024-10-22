!function () {
    const modal = (title, assign = {}) => {
        delete assign.password;
        component.popup({
            submit: '/admin/user/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "头像",
                            name: "avatar",
                            type: "image",
                            placeholder: "请选择图片头像",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 64
                        },
                        {
                            title: "用户名",
                            name: "username",
                            type: "input",
                            placeholder: "请输入用户名"
                        },
                        {
                            title: "邮箱",
                            name: "email",
                            type: "input",
                            placeholder: "请输入邮箱"
                        },
                        {
                            title: "设置登录密码",
                            name: "reset_password",
                            type: "switch",
                            change: (form, value) => {
                                if (value) {
                                    form.show("password");
                                } else {
                                    form.hide("password");
                                    form.setInput("password", "");
                                }
                            }
                        },
                        {
                            title: "新密码",
                            name: "password",
                            type: "input",
                            placeholder: "请填写新的密码",
                            hide: true
                        },
                        {
                            title: "用户组",
                            name: "group_id",
                            type: "select",
                            placeholder: "未开通",
                            dict: "group"
                        },
                        {
                            title: "会员等级",
                            name: "level_id",
                            type: "select",
                            placeholder: "未初始化",
                            dict: "level?pid=" + assign.pid
                        },
                        {
                            title: "可提现额度",
                            name: "withdraw_amount",
                            type: "input",
                            placeholder: "可提现额度"
                        },
                        {
                            title: "状态",
                            name: "status",
                            type: "switch",
                            placeholder: "ACTIVE|BAN",
                            tips: "ACTIVE代表激活，BAN=封禁"
                        },
                    ]
                }
            ],
            assign: assign,
            autoPosition: true,
            height: "auto",
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            done: () => {
                table.refresh();
            }
        });
    }


    const walletChange = (title, user = {}) => {
        component.popup({
            submit: '/admin/user/balanceChange',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "操作对象",
                            name: "username",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(format.user(user));
                            }
                        },
                        {
                            title: "变更类型",
                            name: "type",
                            type: "radio",
                            dict: [
                                {id: 0, name: "充值"},
                                {id: 1, name: "扣款"},
                            ],
                            change: (form, value) => {
                                if (value == 0) {
                                    form.show("is_withdraw");
                                    form.show("is_lifetime");
                                } else {
                                    form.hide("is_withdraw");
                                    form.hide("is_lifetime");
                                }
                            }
                        },
                        {
                            title: "是否可提现",
                            name: "is_withdraw",
                            type: "switch",
                            tips: "本次操作充值的金额是否可以用作提现",
                        },
                        {
                            title: "生涯累计",
                            name: "is_lifetime",
                            type: "switch",
                            tips: "本次充值是否累计到生涯",
                        },
                        {
                            title: "操作金额",
                            name: "amount",
                            type: "input",
                            placeholder: "请输入要操作的金额",
                            default: '1.00'
                        },
                        {
                            title: "备注",
                            name: "remark",
                            type: "input",
                            placeholder: "显示在账单中的备注信息"
                        },
                    ]
                }
            ],
            confirmText: util.icon('icon-jinduquerentubiao') + " 提交操作",
            assign: user,
            autoPosition: true,
            height: "auto",
            width: "480px",
            done: () => {
                table.refresh();
            }
        });
    }

    const table = new Table("/admin/user/get", "#user-table");
    table.setUpdate("/admin/user/save");
    table.setColumns([
        {field: 'id', title: 'ID'},
        {
            field: 'username', title: '会员名', formatter: (id, item) => {
                return format.user(item);
            }
        },

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
            field: 'parent', title: '上级', formatter: (parent, item) => {
                if (!parent) {
                    return '-';
                }
                return format.user(parent);
            }
        },
        {
            field: 'group', title: '用户组', formatter: format.group
        },
        {
            field: 'level', title: '会员等级', formatter: format.group
        },
        {field: 'note', title: '备注', type: 'input', width: 120},
        {field: 'status', title: '状态', type: "switch", text: "激活|封禁", reload: true},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-qianbao',
                    class: 'acg-badge-h-dodgerblue',
                    tips: '充值/扣除余额',
                    click: (event, value, row, index) => {
                        walletChange(util.icon("icon-qianbao") + "<space></space> 余额变更", row);
                    }
                },
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    tips: '修改会员资料',
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-a-xiugai2") + "<space></space> 修改会员", row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    tips: '从数据库移除该会员(危险功能)',
                    click: (event, value, row, index) => {
                        message.dangerPrompt("您正在执行高风险的会员删除操作，需要注意此操作无法恢复数据。如果您只是希望会员无法登录，我们建议您封禁此会员而不是删除。", "我确认删除", () => {
                            component.deleteDatabase("/admin/user/del", [row.id], () => {
                                table.refresh();
                            });
                        });
                    }
                }
            ]
        },
    ]);
    table.setSearch([
        {
            title: "显示范围：整站", name: "display_scope", type: "select", dict: [
                {id: 1, name: "仅主站"},
                {id: 2, name: "仅商家"}
            ], change: (search, val) => {
                if (val == 2) {
                    search.show("user_id");
                } else {
                    search.hide("user_id");
                }

                if (val == 1) {
                    search.show("equal-level_id");
                } else {
                    search.hide("equal-level_id");
                }
            }
        },
        {
            title: "搜索商家",
            name: "user_id",
            type: "remoteSelect",
            dict: "user?type=2",
            hide: true,
            change: (search, val, selected) => {
                if (selected) {
                    search.selectReload("equal-level_id", "level?pid=" + val);
                    search.show("equal-level_id");
                } else {
                    search.selectReload("equal-level_id", "level");
                    search.hide("equal-level_id");
                }
            }
        },
        {
            title: "会员等级",
            name: "equal-level_id",
            type: "select",
            dict: "level",
            hide: true
        },
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
        },
        {
            title: "备注",
            name: "equal-note",
            type: "input"
        },
    ]);
    table.setPagination(10, [10, 20, 30, 50, 100]);
    table.setState("group_id", "group");
    table.setFloatMessage([
        {field: 'email', title: '邮箱'},
        {field: 'app_key', title: '对接密钥'},
        {field: 'api_code', title: '供货码'},
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
        {field: 'lifetime.register_time', title: '账号注册时间'},
        {field: 'lifetime.register_ip', title: '账号注册IP'},
        {field: 'lifetime.register_ua', title: '注册时浏览器', formatter: format.browser},
        {
            field: 'group_id', title: '权限', formatter: (group, item) => {
                group = item.group;

                if (!group) {
                    return '-';
                }
                let html = '';
                if (group.is_merchant == 1) {
                    html += format.badge("商家", "acg-badge-h-green");
                }
                if (group.is_supplier == 1) {
                    html += format.badge("供货商", "acg-badge-h-dodgerblue");
                }
                if (group.is_merchant == 1 && group.is_supplier == 1) {
                    html += format.badge(`自运营(税率:${format.amount((new Decimal(group.tax_ratio)).mul(100).getAmount())}%)`, "acg-badge-h-dodgerblue");
                }
                return html === '' ? '-' : html;
            }
        }
    ]);
    table.onResponse(data => {
        $('.data-count .user-count').html(data.user_count);
        $('.data-count .user-total-balance').html(getVar("CCY") + data.user_total_balance);
    });
    table.render();


    $(`.add-user`).click(() => {
        modal(util.icon("icon-tianjia") + " 添加会员");
    });
}();