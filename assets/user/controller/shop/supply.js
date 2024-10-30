!function () {
    let table;


    const viewItem = (id) => {
        util.post({
            url: "/user/shop/supply/item?id=" + id, done: res => {
                const item = res.data;
                let skus = {};
                item.skus.forEach(s => {
                    skus[s.id] = s;
                });

                const getPrice = (skuId, quantity) => {
                    let price = "0.00";
                    for (const id in skus) {
                        const sk = skus[id];
                        if (skuId == id) {
                            price = sk.stock_price;
                            if (sk?.have_wholesale == true) {
                                sk?.wholesale?.forEach(wholesale => {
                                    if (quantity >= wholesale.quantity) {
                                        price = wholesale.price;
                                    }
                                });
                            }
                        }
                    }
                    return price;
                }

                let forms = [
                    {
                        title: "商品名称",
                        name: "name",
                        type: "custom",
                        complete: (popup, dom) => {
                            dom.html(`<div style="line-height: 36px;">${item.name}</div>`);
                        }
                    },
                    {
                        title: "预估价格",
                        name: "amount",
                        type: "custom",
                        complete: (popup, dom) => {
                            dom.html(`<div style="line-height: 36px;color: #ff5000;font-size: 22px;">${getVar("CCY")}<span class="sku-amount">${item.skus[0].stock_price}</span></div>`);
                        }
                    },
                    {
                        title: "商品类型",
                        name: "repertory_item_sku_id",
                        type: "radio",
                        dict: item.skus,
                        default: item.skus[0].id,
                        complete: (popup, value) => {
                        },
                        change: (popup, value) => {
                            const sku = skus[value];
                            const quantity = popup.getMap("quantity");

                            if (sku.have_wholesale === true) {
                                let html = ``;
                                sku.wholesale?.forEach(wholesale => {
                                    html += `购买数量达到 <b class="fw-bold fs-4">${wholesale.quantity}</b> 件以上，优惠价：<b class="fw-bold fs-6" style="color: #fd5687;">${getVar("CCY")}${wholesale.price}</b><br>`;
                                });
                                popup.setCustom("wholesale", html);
                                popup.show("wholesale");
                            } else {
                                popup.hide("wholesale");
                            }

                            $(`.${popup.unique} .component-stock .sku-stock`).html(sku.stock);
                            $(`.${popup.unique} .component-amount .sku-amount`).html(format.amountRemoveTrailingZeros(new Decimal(getPrice(sku.id, quantity), 6).mul(quantity).getAmount(6)));
                        }
                    }
                ];

                item.widget.forEach(w => {
                    forms.push(WidgetUtil.widgetToPopup(w));
                });

                forms = forms.concat([
                    {
                        title: "当前库存",
                        name: "stock",
                        type: "custom",
                        complete: (popup, dom) => {
                            dom.html(`<div style="line-height: 36px;color: #1baf3b;"><span class="sku-stock">${item.skus[0].stock}</span></div>`);
                        }
                    },
                    {
                        title: "支持批发",
                        name: "wholesale",
                        type: "custom",
                        complete: (popup, dom) => {
                            if (item.skus[0]?.have_wholesale === true) {
                                let html = ``;
                                item.skus[0]?.wholesale?.forEach(wholesale => {
                                    html += `购买数量达到 <b class="fw-bold fs-4">${wholesale.quantity}</b> 件以上，优惠价：<b class="fw-bold fs-6" style="color: #fd5687;">${getVar("CCY")}${wholesale.price}</b><br>`;
                                });
                                dom.html(html);
                            }
                        },
                        hide: item.have_wholesale == false
                    },
                    {
                        title: "进货数量",
                        name: "quantity",
                        type: "number",
                        placeholder: "请输入进货数量",
                        regex: {
                            value: "^[1-9]\\d*$",
                            message: "进货数量必须大于0"
                        },
                        default: 1,
                        change: (popup, value) => {
                            if (value > 0) {
                                $(`.${popup.unique} .component-amount .sku-amount`).html(format.amountRemoveTrailingZeros(new Decimal(getPrice(popup.getMap("repertory_item_sku_id"), value), 6).mul(value).getAmount(6)));
                            }
                        }
                    },
                    {
                        title: "商品属性",
                        name: "attr",
                        type: "custom",
                        hide: item.attr.length === 0,
                        complete: (popup, dom) => {
                            let html = `<table class="table table-striped"><tbody>`;
                            item.attr.forEach(e => {
                                html += `<tr>
                                                <td>${e.name}</td>
                                                <td class="sku-attr-value">${e.value}</td>
                                            </tr>`;
                            });
                            dom.html(html + `</tbody></table>`);
                        }
                    },
                    {
                        title: "商品介绍",
                        name: "introduce",
                        type: "custom",
                        complete: (popup, dom) => {
                            dom.html(`<div class="pt-2">${item.introduce}</div>`);
                        }
                    }]);

                component.popup({
                    submit: '/user/shop/supply/trade',
                    tab: [
                        {
                            name: util.icon("icon-chakanxiangqing") + "<space></space>" + item.name,
                            form: forms
                        },
                    ],
                    assign: {},
                    message: false,
                    autoPosition: true,
                    content: {
                        css: {
                            height: "auto",
                            overflow: "inherit"
                        }
                    },
                    height: "auto",
                    width: "760px",
                    confirmText: util.icon("icon-goumai") + "立即下单",
                    done: (result, data) => {
                        let sku = skus[data['repertory_item_sku_id']];
                        component.popup({
                            tab: [
                                {
                                    name: util.icon("icon-dingdan2", "icon-18px") + `<span class="text-success acg-bold">${item.name}</span>` + ` <span class="text-danger">(${sku.name})</span>` + " 的宝贝信息",
                                    form: [
                                        {
                                            name: "treasure",
                                            type: "textarea",
                                            hide: true,
                                            placeholder: "无",
                                            default: result.data.contents,
                                            height: "100%",
                                            disabled: true,
                                            complete: (popup, val, dom) => {
                                                dom.parent().parent().parent().parent().css("padding", "0px");
                                                dom.get(0).style.setProperty("border-radius", "0px", "important");
                                                dom.css("height", (dom.parent().parent().parent().parent().parent().height() - 70) + "px");
                                                dom.parent().parent().fadeIn("slow");
                                            }
                                        },
                                        {
                                            name: "handle",
                                            type: "custom",
                                            complete: (popup, dom) => {
                                                dom.html(`<div style="text-align: center;"><span class="btn-order-info-copy"><button type="button" class="btn btn-warning btn-sm open-logs me-2 mb-2"><i class="fa fa-fw fa-copy me-1"></i>复制</button></span><button type="button" class="btn btn-info btn-sm btn-order-info-download me-2 mb-2"><i class="fa fa-fw fa-download me-1"></i>下载</button></div>`);
                                                $('.btn-order-info-copy').click(function () {
                                                    util.copyTextToClipboard(popup.getMap("treasure"), () => {
                                                        message.success("复制成功！");
                                                    }, () => {
                                                        message.success("复制失败，请手动复制！");
                                                    });
                                                });
                                                $('.btn-order-info-download').click(() => {
                                                    message.error("暂未实现此功能");
                                                });
                                            }
                                        },
                                    ]
                                }
                            ],
                            width: "580px",
                            height: "580px",
                            end: () => {
                            }
                        });
                    }
                });
            }
        });
    }


    table = new Table("/user/shop/supply/get", "#shop-supply-table");
    table.setColumns([
        {checkbox: true},
        {field: 'category.name', title: '仓库'},
        {field: 'name', title: '货物名称'},
        {
            field: 'sku', title: 'SKU/进货价', formatter: skus => {
                let html = "";

                skus.forEach(sku => {
                    html += `<span type="button" class="acg-badge-h acg-badge-h-dodgerblue me-1 mb-1">${sku.name} / ${format.money(sku.stock_price, "#3ad782", false)}</span>`;
                });

                return html;
            }
        },
        {
            field: 'operation', type: 'button', buttons: [
                {
                    icon: 'icon-chakanxiangqing',
                    title: '查看',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        viewItem(row.id);
                    }
                }
            ]
        },
    ]);
    table.setPagination(10, [10, 20, 30, 50]);

    table.setSearch([
        {
            title: "分类",
            name: "equal-repertory_category_id",
            type: "treeSelect",
            dict: "repertoryCategory",
            default: 0,
            search: true
        },
        {title: "货物关键词", name: "search-name", type: "input"},
        {title: "对接码", name: "api_code", type: "input"},
    ]);

    table.render();


    $('.add-supply-item').click(() => {
        let data = table.getSelectionIds();
        if (data.length == 0) {
            layer.msg(i18n("请勾选要对接的商品"));
            return;
        }

        component.popup({
            submit: (res, index) => {
                res.data = data;
                util.post("/user/shop/supply/dock", res, ret => {
                    layer.close(index);
                    message.alert("商品接入完成");
                })
            },
            confirmText: util.icon("icon-jinduquerentubiao") + " " + i18n("立即导入"),
            tab: [
                {
                    name: util.icon("icon-web__kuaisuduijie") + " 选择你要入库的分类",
                    form: [
                        {
                            title: "商品分类",
                            name: "category_id",
                            type: "treeSelect",
                            placeholder: "请选择你的商品分类",
                            dict: "shopCategory",
                            search: true,
                            required: true,
                            parent: false
                        },
                        {
                            title: "同步模版",
                            name: "markup_id",
                            type: "select",
                            placeholder: "请选择模版",
                            dict: "itemMarkupTemplate",
                            required: true
                        }
                    ]
                }
            ],
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            autoPosition: true,
            height: "auto",
            width: "580px",
            maxmin: false
        });

    });
}();