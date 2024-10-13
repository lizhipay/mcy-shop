!function () {

    const role = [
        util.icon("icon-guanfang", "timeline-event-icon"),
        util.icon("icon-baby", "timeline-event-icon"),
        util.icon("icon-wode", "timeline-event-icon", "icon-40px")
    ];

    const roleName = ["您(平台)", "供货商", "顾客"];

    const broadcast = new Broadcast(".report-voice-broadcast", "/assets/common/audio/report");


    const reportApply = (unique, item) => {
        let refundAmount = 0;
        const sku = item?.order_item?.sku?.repertory_item_sku
        if (sku) {
            refundAmount = (new Decimal(sku.user_id > 0 ? sku.supply_price : sku.stock_price)).sub(sku.cost).getAmount(2);
        }

        component.popup({
            submit: '/admin/shop/report/order/handle',
            confirmText: util.icon("icon-anquan4") + " " + i18n("确认处理"),
            tab: [
                {
                    name: util.icon("icon-anquan4") + " 回复/处理该问题",
                    form: [
                        {
                            name: "report_id",
                            type: "input",
                            default: item.id,
                            hide: true
                        },
                        {
                            title: "处理方式",
                            name: "handle_type",
                            type: "select",
                            placeholder: "请选择",
                            dict: "order_report_handle_type",
                            default: 0,
                            required: true,
                            change: (form, val) => {
                                val == 1 ? form.show('treasure') : form.hide('treasure');
                                val == 2 ? form.show('refund_amount') : form.hide('refund_amount');
                                val == 2 ? form.show('refund_merchant_amount') : form.hide('refund_merchant_amount');

                                if (val == 2 && item?.order_item?.refund_mode == 0) {
                                    form.setSelected("handle_type", 0);
                                    layer.msg("该商品不支持退款");
                                    return;
                                }

                                if (val == 3 && item?.order_item?.refund_mode != 2) {
                                    form.setSelected("handle_type", 0);
                                    layer.msg("该商品不支持全额退款");
                                }
                            }
                        },
                        {
                            title: "补发内容",
                            name: "treasure",
                            type: "textarea",
                            placeholder: "这里要填写补发的商品内容，会替换原先发货的商品内容",
                            height: 150,
                            hide: true
                        },
                        {
                            title: "退款金额(顾客)",
                            name: "refund_amount",
                            type: "input",
                            placeholder: "退款金额",
                            default: refundAmount,
                            hide: true
                        },
                        {
                            title: "退款金额(商家)",
                            name: "refund_merchant_amount",
                            type: "input",
                            placeholder: "退款金额",
                            default: refundAmount,
                            hide: true
                        },
                        {
                            title: "回复内容",
                            name: "message",
                            type: "textarea",
                            placeholder: "请填写要回复的内容",
                            height: 260,
                            required: true
                        },
                        {
                            title: "相关截图",
                            name: "image_url",
                            type: "image",
                            placeholder: "请选择相关截图",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 200
                        }
                    ]
                }
            ],
            done: () => {
                //刷新聊天记录
                loadMessage(unique, item);
            },
            autoPosition: true,
        });
    }


    const loadMessage = (unique, reportOrder) => {
        let latest = 0, token = util.generateRandStr(16), tokenKey = `order_report_message_heartbeat_${reportOrder.id}`;
        localStorage.setItem(tokenKey, token);
        util.post({
            url: "/admin/shop/report/order/message",
            data: {report_id: reportOrder.id},
            loader: false,
            done: res => {
                let html = `<ul class="mcy-timeline mcy-timeline-modern pull-t">`;
                res.data.forEach(item => {
                    let image = ``;
                    if (item.image_url) {
                        image = `<div class="row items-push js-gallery img-fluid-100">
                      <div class="col-sm-6 col-xl-3">
                        <a class="img-link img-link-zoom-in img-lightbox" target="_blank" href="${item.image_url}">
                          <img class="img-fluid" style="max-height: 200px;" src="${item.image_url}" alt="">
                        </a>
                      </div>
                    </div>`;
                    }

                    html += `<li class="timeline-event">
                  <div class="timeline-event-time">${format.pastTime(item.create_time)}</div>
                  ${role[item.role]}
                  <div class="timeline-event-block">
                    <p class="fw-semibold">${i18n(roleName[item.role])}</p>
                    <p>${item.message}</p>
                    ${image}
                  </div>
                </li>`;
                });

                if (reportOrder.status != 3) {
                    html += `<li class="timeline-event"><div class="timeline-event-block">
<button type="button" class="btn btn-sm acg-badge-h-dodgerblue btn-handle-${reportOrder.id}">${util.icon("icon-huifupingluns")} ${i18n('回复/处理')}</button>
</div>
</li>
`;
                }
                html += `</ul><div id="${unique}"></div>`;
                $(`.${unique}`).html(html).fadeIn(150);
                setTimeout(() => {
                    window.location.hash = "#";
                    window.location.hash = "#" + unique;
                }, 100);
                $('.btn-handle-' + reportOrder.id).click(() => {
                    reportApply(unique, reportOrder);
                });

                util.timer(() => {
                    return new Promise(resolve => {
                        if (localStorage.getItem(tokenKey) != token || $(`.${unique}`).length == 0) {
                            resolve(false);
                            return;
                        }
                        util.post({
                            url: "/admin/shop/report/order/heartbeat",
                            loader: false,
                            data: {report_id: reportOrder.id},
                            done: res => {
                                if (latest == 0) {
                                    latest = res.data.latest;
                                } else if (latest != res.data.latest) {
                                    loadMessage(unique, res.data?.order ?? reportOrder);
                                    broadcast.play("message");
                                    $(`.order-report-status-${reportOrder.id}`).html(_Dict.result("order_report_status", res.data?.order?.status ?? 0));
                                }
                                latest = res.data.latest;
                                resolve(true);
                            }
                        })
                    });
                }, 3000);
            }
        });
    }


    const table = new Table("/admin/shop/report/order/get", "#shop-report-order-table");
    table.setPagination(12, [12, 20, 50, 100]);
    table.setColumns([
        {
            field: 'order_item', title: '订单号', formatter: (item) => {
                if (!item || !item.order) {
                    return '-';
                }
                return item.order.trade_no;
            }
        },
        {
            field: 'supply', title: '供货商', formatter: format.user
        },
        {
            field: 'merchant', title: '商家', formatter: format.user
        },
        {
            field: 'customer', title: '顾客', formatter: format.customer
        },
        {
            field: 'order_item', title: '商品', formatter: (item) => {
                if (!item || !item.item) {
                    return '-';
                }
                return format.item(item.item);
            }
        },
        {
            field: 'order_item', title: 'SKU', formatter: (item) => {
                if (!item || !item.sku) {
                    return '-';
                }
                return format.item(item.sku);
            }
        },
        {
            field: 'order_item', title: '订单金额', formatter: (item) => {
                if (!item) {
                    return '-';
                }
                return format.money(item.amount, "#29da32");
            }
        },
        {
            field: 'status', title: '状态', formatter: (status, item) => {
                return `<span class="order-report-status-${item.id}">${_Dict.result("order_report_status", status)}</span>`;
            }
        },
        {field: 'type', title: '问题类型', dict: "order_report_type"},
        {field: 'expect', title: '维权方式', dict: "order_report_expect"},
        {
            field: 'refund_amount', title: '退款金额', formatter: (amount, item) => {
                if (amount == 0 || !amount) {
                    return '-';
                }
                return format.money(amount, "#19bf5d");
            }
        },
        {field: 'create_time', title: '申请时间'},
        {field: 'handle_type', title: '处理方式', dict: "order_report_handle_type"},
        {
            field: 'trade', type: 'button', buttons: [
                {
                    icon: 'icon-querenshouhuo',
                    class: 'acg-badge-h-dodgerblue',
                    title: '完成',
                    click: (event, value, row, index) => {
                        message.ask("您确定要完成此投诉订单吗？一旦确认，该用户将无法对当前订单进行任何售后申诉。请谨慎操作。", () => {
                            util.post("/admin/shop/report/order/finish", {item_id: row.order_item_id}, res => {
                                table.refresh(true);
                            });
                        });
                    },
                    show: item => {
                        if (item.status != 3) {
                            return true;
                        }
                        return false;
                    }
                }
            ]
        },
    ]);
    table.setSearch([
        {
            title: "订单号",
            name: "trade_no",
            type: "input",
            width: 260
        },
        {
            title: "供货商",
            name: "equal-supply_id",
            type: "remoteSelect",
            dict: "user?type=1"
        },
        {
            title: "商家",
            name: "equal-merchant_id",
            type: "remoteSelect",
            dict: "user?type=2"
        },
        {
            title: "顾客",
            name: "equal-customer_id",
            type: "remoteSelect",
            dict: "user"
        },
        {title: "申请时间", name: "between-create_time", type: "date"}
    ]);
    table.setState("status", "order_report_status");
    table.setDetail(item => {
        let unique = util.generateRandStr();
        loadMessage(unique, item);
        return `<div class="${unique}" style="display: none;">正在加载..</div>`;
    });
    table.render();
}();