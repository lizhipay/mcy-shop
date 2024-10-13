!function () {
    const $changeLeft = $(".quantity-change .change-left"),
        $changeRight = $(".quantity-change .change-right"),
        $changeQuantity = $(".quantity-change .change-quantity"),
        $cartWidgetEdit = $(".cart-widget-edit"),
        $cartItemDel = $(".cart-item-del"),
        $btnCartClear = $(".btn-cart-clear"),
        $btnCartBill = $(".btn-cart-bill"),
        $totalAmount = $(".cart-total-amount");

    function changeQuantity(itemId, quantity) {
        util.post({
            url: "/shop/cart/changeQuantity",
            data: {
                quantity: quantity,
                item_id: itemId
            },
            loader: {
                enable: false
            },
            done: () => {
                changeAmount(itemId);
                changeTotalAmount();
            },
            error: err => {
                message.error(err?.msg);
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        });
    }

    function changeTotalAmount() {
        util.post({
            url: "/shop/cart/getAmount",
            loader: {
                enable: false
            },
            done: res => {
                $totalAmount.html(getVar("CCY") + res.data.amount);
            }
        });
    }

    function changeAmount(itemId) {
        util.post({
            url: "/shop/cart/getItem",
            data: {
                item_id: itemId
            },
            loader: {
                enable: false
            },
            done: res => {
                $(".cart-amount-" + itemId).html(getVar("CCY") + res.data.amount);
                $(".cart-price-" + itemId).html(getVar("CCY") + res.data.price);
            }
        });
    }

    function clearCart(done = null) {
        util.post({
            url: "/shop/cart/clear",
            done: () => {
                typeof done === "function" && done();
            }
        });
    }

    $changeLeft.click(function () {
        let $quantityHandle = $(this).parent().find(".change-quantity");
        let itemId = $(this).parent().attr("data-id"), quantity = parseInt($quantityHandle.val());
        if (quantity > 1) {
            quantity = quantity - 1;
            $quantityHandle.val(quantity);
            changeQuantity(itemId, quantity);
        }
    });

    $changeRight.click(function () {
        let $quantityHandle = $(this).parent().find(".change-quantity");
        let itemId = $(this).parent().attr("data-id"), quantity = parseInt($quantityHandle.val());
        quantity = quantity + 1;
        $quantityHandle.val(quantity);
        changeQuantity(itemId, quantity);
    });

    $changeQuantity.on("input", function () {
        let quantity = parseInt($(this).val());
        if (quantity <= 0 || isNaN(quantity)) {
            $(this).val("1");
        }
    });

    $changeQuantity.change(function () {
        let itemId = $(this).parent().parent().attr("data-id"), quantity = parseInt($(this).val());
        changeQuantity(itemId, quantity);
    });

    $cartWidgetEdit.click(function () {
        let itemId = $(this).attr("data-id");
        util.post("/shop/cart/getItem", {item_id: itemId}, res => {
            let forms = [];

            for (let i = 0; i < res.data.item.widget.length; i++) {
                let widget = res.data.item.widget[i];
                forms.push(WidgetUtil.widgetToPopup(widget));
            }

            component.popup({
                submit: '/shop/cart/updateOption?item_id=' + itemId,
                tab: [
                    {
                        name: `${util.icon("icon-bianji")}<space></space>修改备注信息`,
                        form: forms
                    }
                ],
                assign: res.data.option,
                autoPosition: true,
                content: {
                    css: {
                        height: "auto",
                        overflow: "inherit"
                    }
                },
                height: "auto",
                width: "580px",
                done: () => {
                    window.location.reload();
                }
            });

        });
    });

    $cartItemDel.click(function () {
        let itemId = $(this).attr("data-id");
        $(this).parent().parent().remove();
        util.post({
            url: "/shop/cart/delItem",
            data: {
                item_id: itemId
            },
            loader: {
                enable: false
            },
            done: () => {
                changeTotalAmount();
            }
        });
    });

    $btnCartClear.click(function () {
        clearCart(() => {
            window.location.reload();
        });
    });

    $btnCartBill.click(function () {
        util.post({
            url: "/shop/cart/items",
            done: res => {
                let items = [];
                res.data.forEach(item => {
                    item.option.quantity = item.quantity;
                    items.push(item.option);
                });
                util.post("/shop/order/trade", {
                    items: items
                }, res => {
                    $btnCartBill.attr("disabled", true);
                    clearCart(() => {
                        window.location.href = "/checkout?tradeNo=" + res.data.trade_no;
                    });
                }, error => {
                    if (error.code == 9) {
                        message.ask("您有一个订单尚未支付，如需继续下单，请先处理未支付的订单。", () => {
                            window.open("/checkout?tradeNo=" + error.msg);
                        }, "下单频繁", "去处理");
                        return;
                    }
                    layer.msg(error.msg);
                });
            }
        })
    });

}();