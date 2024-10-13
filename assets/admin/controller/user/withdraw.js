!function () {
    const table = new Table("/admin/user/withdraw/get", "#user-withdraw-table");
    table.setColumns([
        {
            field: 'user', title: '会员', formatter: format.user
        },

        {
            field: 'amount', title: '提现金额', formatter: amount => {
                return format.money(amount , "#ff1d1d");
            }
        },
        {
            field: 'card.bank', title: '收款银行', formatter: bank => {
                return format.bank(bank);
            }
        },
        {
            field: 'user', title: '姓名', formatter: user => {
                return user?.identity?.name;
            }
        },
        {
            field: 'card.card_no', title: '银行卡号'
        },
        {
            field: 'card.card_image',
            title: '银行卡照片',
            type: "image"
        },
        {field: 'status', title: '状态', dict: 'user_withdraw_status'},
        {field: 'handle_message', title: '回复信息'},
        {
            field: 'operation', title: '处理', type: 'button', buttons: [
                {
                    icon: 'icon-chuli',
                    title: '处理',
                    class: 'btn-table-primary',
                    click: (event, value, row, index) => {
                        component.popup({
                            submit: '/admin/user/withdraw/processed',
                            confirmText: util.icon("icon-jinduquerentubiao") + " 确认处理",
                            autoPosition: true,
                            height: "auto",
                            tab: [
                                {
                                    name: util.icon("icon-chuli") + " 处理提现",
                                    form: [
                                        {
                                            title: "打款信息",
                                            name: "bank_info",
                                            type: "custom",
                                            complete: (form, dom) => {
                                                let cardImage = ``;
                                                if (row?.card?.card_image) {
                                                    cardImage = `<div class="w-item"><span class="w-title">${i18n("收款卡照片")}</span><span class="w-text"><a href="${row?.card?.card_image}" target="_blank"><img src="${row?.card?.card_image}"></a></span><button class="w-copy invisible">${i18n("复制")}</button></div>`;
                                                }
                                                dom.html(
                                                    `<div class="withdraw-info">
                                                           <div class="w-item"><span class="w-title">${i18n("提现金额")}</span><span class="w-text text-success">${getVar("CCY")}${row.amount}</span><button type="button" data-text="${row.amount}" class="w-copy">${i18n("复制")}</button></div>
                                                           <div class="w-item"><span class="w-title">${i18n("收款银行")}</span><span class="w-text">${format.bank(row?.card?.bank)}</span><button class="w-copy invisible">${i18n("复制")}</button></div>
                                                           <div class="w-item"><span class="w-title">${i18n("收款人")}</span><span class="w-text">${row?.user?.identity?.name}</span><button type="button"  data-text="${row?.user?.identity?.name}" class="w-copy">${i18n("复制")}</button></div>
                                                           <div class="w-item"><span class="w-title">${i18n("收款卡号")}</span><span class="w-text">${row?.card?.card_no}</span><button type="button" data-text="${row?.card?.card_no}" class="w-copy">${i18n("复制")}</button></div>
                                                           ${cardImage}
                                                     </div>`
                                                );
                                                dom.find(".w-copy").click(function () {
                                                    util.copyTextToClipboard($(this).data("text"), () => {
                                                        layer.msg(i18n("复制成功"));
                                                    }, () => {
                                                        layer.msg(i18n("复制失败，请手动复制"));
                                                    });
                                                });

                                            }
                                        },
                                        {
                                            title: "处理状态",
                                            name: "status",
                                            type: "radio",
                                            dict: [
                                                {id: 1, name: "提现已到账"},
                                                {id: 2, name: "提现被驳回"},
                                            ],
                                            required: true,
                                            change: (form, val) => {
                                                if (val == 2) {
                                                    form.show("lock_card");
                                                    form.show("message");
                                                } else {
                                                    form.hide("lock_card");
                                                    form.hide("message");
                                                }
                                            }
                                        },
                                        {
                                            title: "锁定此银行卡",
                                            name: "lock_card",
                                            type: "switch",
                                            hide: true
                                        },
                                        {
                                            title: "处理消息",
                                            name: "message",
                                            type: "input",
                                            placeholder: "请输入处理消息，可为空",
                                            hide: true
                                        }
                                    ]
                                }
                            ],
                            assign: row,
                            done: () => {
                                table.refresh();
                            }
                        });
                    },
                    show: item => {
                        return item.status == 0;
                    }
                }
            ]
        },
    ]);
    table.setFloatMessage([
        {field: 'trade_no', title: '流水号'},
        {field: 'create_time', title: '提现时间'},
        {field: 'handle_time', title: '处理时间'},
    ]);
    table.setPagination(10, [10, 30, 50, 100, 500]);
    table.setSearch([
        {
            title: "流水号",
            name: "equal-trade_no",
            type: "input"
        },
        {
            title: "搜索会员",
            name: "equal-user_id",
            type: "remoteSelect",
            dict: "user"
        },
        {title: "提现时间", name: "between-create_time", type: "date"},
    ]);
    table.setState("status", "user_withdraw_status");
    table.onResponse(res => {
        $('.data-count .withdraw-amount').html(res.withdraw_amount);
        $('.data-count .not-processed').html(res.not_processed);
        $('.data-count .processed').html(res.processed);
        $('.data-count .reject-processed').html(res.reject_processed);
    });
    table.render();
}();