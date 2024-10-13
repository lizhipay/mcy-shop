!function () {
    let table = null;
    const modal = (title, assign = {}) => {
        component.popup({
            submit: '/user/bank/card/save',
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: false,
                            name: "domain_tips",
                            type: "custom",
                            complete: (form, dom) => {
                                dom.html(`
<div class="block-tips">
<p>请注意，根据相关金融安全规定，用户绑定的银行卡必须严格符合实名认证要求，确保银行卡账户的实名信息与本站实名信息一致。若存在不符合情况，本站将依法采取措施，包括但不限于对相关银行卡账户执行强制冻结。请各位用户务必留意，避免因信息不匹配导致不必要的资金损失和法律责任。</p>
</div>
                                    `);

                            }
                        },
                        {
                            title: "所属银行",
                            name: "bank_id",
                            type: "select",
                            placeholder: "请选择银行",
                            dict: "bank",
                            required: true
                        },
                        {
                            title: "银行卡号",
                            name: "card_no",
                            type: "input",
                            placeholder: "请输入银行卡号",
                            tips: "银行机构下发的卡号，比如支付宝就填写支付宝账号，微信就填写微信账号，如果是实体银行卡，就填写银行卡号即可",
                            required: true
                        },
                        {
                            title: "补充银行卡照片",
                            name: "card_image_switch",
                            type: "switch",
                            change: (form, val) => {
                                val ? form.show("card_image") : form.hide("card_image");
                            },
                            tips: "特殊情况下需要补充照片，比如微信收款码，支付宝收款码等"
                        },
                        {
                            title: "上传照片",
                            name: "card_image",
                            type: "image",
                            uploadUrl: '/user/upload',
                            photoAlbumUrl: '/user/upload/get',
                            width: 320,
                            hide: true,
                            placeholder: "请选择补充照片",
                            tips: "一般此处上传收款码"
                        }
                    ]
                }
            ],
            assign: assign,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            autoPosition: true,
            height: "auto",
            done: () => {
                table.refresh();
            }
        });
    }
    table = new Table("/user/bank/card/get", "#bank-card-table");
    table.disablePagination();
    table.setColumns([
        {field: 'bank', title: '银行名称', formatter: format.category},
        {field: 'card_no', title: '银行卡号'},
        {field: 'card_image', title: '银行卡照片(补充)', type: "image"},
        {field: 'status', title: '状态', dict: "user_bank_card_status"},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-shanchu1',
                    title: '解绑此卡',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        component.deleteDatabase("/user/bank/card/del", [row.id], () => {
                            table.refresh();
                        });
                    }
                }
            ]
        },
    ]);

    table.setFloatMessage([
        {field: 'today_withdraw_amount', title: '今日提现'},
        {field: 'yesterday_withdraw_amount', title: '昨日提现'},
        {field: 'weekday_withdraw_amount', title: '本周提现'},
        {field: 'month_withdraw_amount', title: '本月提现'},
        {field: 'last_month_withdraw_amount', title: '上月提现'},
        {field: 'total_withdraw_amount', title: '总提现'}
    ]);

    table.render();

    $('.add-bank-card').click(() => {
        modal(util.icon("icon-tianjia") + "<space></space>绑定银行卡");
    });
}();