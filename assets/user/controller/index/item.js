!function () {


    /**
     * 图片滚动
     * @param selector
     */
    function enableResponsiveScroll(selector) {
        const $container = $(selector);
        let lastMouseX = 0;
        let isFirstMove = true; // 用于PC端首次移动检测
        let isDragging = false; // 移动端拖动标志
        let startX, scrollStartX; // 移动端开始触摸的X坐标和滚动起始位置

        // PC端逻辑
        function handleMouseMove(e) {
            const mouseX = e.clientX;

            if (isFirstMove) {
                lastMouseX = mouseX;
                isFirstMove = false;
                return;
            }

            const containerRect = $container.get(0).getBoundingClientRect();
            const containerCenterX = containerRect.left + containerRect.width / 2;
            const mouseSpeed = mouseX - lastMouseX;
            lastMouseX = mouseX;

            $container.scrollLeft($container.scrollLeft() + mouseSpeed);
        }

        // 移动端逻辑
        function handleTouchStart(e) {
            isDragging = true;
            startX = e.originalEvent.touches[0].pageX;
            scrollStartX = $container.scrollLeft();
        }

        function handleTouchMove(e) {
            if (isDragging) {
                let currentX = e.originalEvent.touches[0].pageX;
                let dx = currentX - startX;
                $container.scrollLeft(scrollStartX - dx);
            }
        }

        function handleTouchEnd() {
            isDragging = false;
        }

        // 根据设备类型绑定事件
        if (util.isPc()) {
            $container.on('mousemove', handleMouseMove);
            $container.on('mouseenter', function () {
                isFirstMove = true;
            });
            $container.on('mouseleave', function () {
                isFirstMove = true;
                lastMouseX = 0;
            });
        } else {
            $container.on('touchstart', handleTouchStart)
                .on('touchmove', handleTouchMove)
                .on('touchend', handleTouchEnd);
        }
    }

    /**
     * 切换图片
     */
    function enableSwitchImage(clickSelector, imageSelector) {
        const $clickSelector = $(clickSelector);
        const $imageSelector = $(imageSelector);
        $clickSelector.click(function () {
            const skuId = $(this).attr("data-skuId");
            switchSku($('.sku-item-btn' + skuId), $quantity, $skuItem, $itemAmount);
            $clickSelector.removeClass("image-active");
            $imageSelector.css({
                "filter": "blur(5px)",
                "transition": "filter 3s"
            });
            $(this).addClass("image-active");
            $imageSelector.off('load');
            $imageSelector.on('load', function () {
                $(this).css({
                    "filter": "none",
                    "transition": "none"
                });
            });
            $imageSelector.attr("src", $(this).find("img").attr("data-src"));
        });
    }

    /**
     * 切换SKU
     * @param $this
     * @param $quantity
     * @param $skuItem
     * @param $itemAmount
     */
    function switchSku($this, $quantity, $skuItem, $itemAmount) {
        $skuItem.removeClass("sku-current");
        $this.addClass("sku-current");
        const stock = $this.attr("data-stock"),
            stockAvailable = $this.attr("data-stock-available"),
            skuId = $this.attr("data-skuId"), quantity = $quantity.val(), price = getPrice(skuId, quantity);
        const amount = (new Decimal(price, 6)).mul(quantity).getAmount(6);
        $skuId.val(skuId);
        $itemAmount.html(`${getVar("CCY")}<span style="color: #ff5000;font-size: 26px;">${format.amountRemoveTrailingZeros(amount)}</span>`);


        if (isSkuHaveWholesale(skuId)) {
            $skuWholesale.show();
        } else {
            $skuWholesale.hide();
        }
        switchCheckoutButton(stock, stockAvailable);
        initializeMinQuantityRestriction($quantity, skuId);
    }

    /**
     * 切换购买按钮
     * @param stock
     * @param stockAvailable
     */
    function switchCheckoutButton(stock, stockAvailable) {
        if (stockAvailable == "1") {
            $haveStock.removeClass("d-hide");
            $noStock.addClass("d-hide");
            $skuStock.removeClass("bg-light-gray").addClass("bg-lime-green");
        } else {
            $noStock.removeClass("d-hide");
            $haveStock.addClass("d-hide");
            $skuStock.removeClass("bg-lime-green").addClass("bg-light-gray");
        }
        $skuStock.html(stock);
    }

    function validator(widgets, values) {
        /*        if (!(/^.{6,}$/.test(values['contact']))) {
                    message.error("联系方式最低不少于6个字符");
                    return false;
                }*/

        if (!(/^(?!0)\d{1,11}$/.test(values['quantity']))) {
            message.error("购买数量必须大于0，且不能超过11位");
            return false;
        }

        for (let i = 0; i < widgets.length; i++) {
            let widget = widgets[i];
            if (widget.regex !== "") {
                const pattern = new RegExp(widget.regex);
                if (!pattern.test(values[widget.name])) {
                    message.error(widget.error);
                    return false;
                }
            }
        }

        return true;
    }


    function getPrice(skuId, quantity) {
        let price = "0.00";
        item?.sku?.forEach(sku => {
            if (skuId == sku.id) {
                price = sku.price;
                if (sku?.have_wholesale == true) {
                    sku?.wholesale?.forEach(wholesale => {
                        if (quantity >= wholesale.quantity) {
                            price = wholesale.price;
                        }
                    });
                }
            }
        });
        return price;
    }

    function isSkuHaveWholesale(skuId) {
        let state = false;
        item?.sku?.forEach(sku => {
            if (skuId == sku.id) {
                state = sku?.have_wholesale == true;
            }
        });
        return state;
    }

    function checkQuantityRestriction($handle, skuId, quantity) {
        let state = true;
        item?.sku?.forEach(sku => {
            if (skuId == sku.id) {
                if (sku?.quantity_restriction?.min > quantity) {
                    layer.msg(`购买数量不能低于 <b class="fw-bold fs-4">${sku?.quantity_restriction?.min}</b> 件`);
                    state = false
                    $handle.val(sku?.quantity_restriction?.min);
                }
                if (sku?.quantity_restriction?.max < quantity && sku?.quantity_restriction?.max != 0) {
                    layer.msg(`购买数量不能大于 <b class="fw-bold fs-4">${sku?.quantity_restriction?.max}</b> 件`);
                    state = false
                    $handle.val(sku?.quantity_restriction?.max);
                }
            }
        });
        return state;
    }

    function initializeMinQuantityRestriction($handle, skuId) {
        item?.sku?.forEach(sku => {
            if (skuId == sku.id) {
                $handle.val(sku?.quantity_restriction?.min);
            }
        });
    }


    function updateItemAmount(price, quantity) {
        const amount = (new Decimal(price, 6)).mul(quantity).getAmount(6);
        $itemAmount.html(`${getVar("CCY")}<span style="color: #ff5000;font-size: 26px;">${format.amountRemoveTrailingZeros(amount)}</span>`);
    }


    function autoItemImageHeight() {
        const contentHeight = $itemTitleContent.height() + $itemControls.height();
        if (contentHeight > 270) {
            $itemImageMain.height(232 + (contentHeight - 270));
        } else {
            $itemImageMain.height(232);
        }
    }


    let $haveStock = $('.shop-checkout-btn.have-stock');
    let $noStock = $('.shop-checkout-btn.no-stock');
    let $skuStock = $('.sku-quantity-wrapper .sku-stock');
    let $inputControls = $('.item-controls .table .sku-quantity');
    let $skuItem = $('.sku-wrapper .sku-item');
    let $itemAmount = $('.item-title-content .item-amount');
    let $quantity = $('.item-controls input[name=quantity]');
    let $skuId = $('.item-controls input[name=sku_id]');
    let $itemControls = $('.item-controls');
    let $addCart = $('.btn-add-cart');
    let $buyNow = $('.btn-buy-now');
    let $itemImageMain = $('.item-image-main');
    let $productInfoArea = $('.product-info-area');
    let $itemTitleContent = $('.item-title-content');
    let $skuWholesale = $('.sku-wholesale');
    let item = getVar("item");

    enableResponsiveScroll('.item-image-list');
    enableSwitchImage('.item-image-sku-mini', '.item-image-show');

    //const startHeight = 350;


    //console.log($productInfoArea.height(), startHeight);

    $inputControls.before(WidgetUtil.getWidget(item.widget));

    if (util.isPc()) {
        autoItemImageHeight();
        component.resizeObserver($itemControls, event => {
            autoItemImageHeight();
        });
    }

    $quantity.change(function () {
        if (!checkQuantityRestriction($(this), $skuId.val(), $(this).val())) {
            return;
        }
        if ($(this).val() <= 0) {
            $(this).val(1);
            return;
        }
        const quantity = $(this).val(), price = getPrice($skuId.val(), quantity);
        updateItemAmount(price, quantity);
    });

    $skuItem.click(function () {
        const skuId = $(this).attr("data-skuId");
        switchSku($(this), $quantity, $skuItem, $itemAmount);
        $('.sku-image-btn' + skuId).click();
    });


    $addCart.click(function () {
        let data = util.arrayToObject($('.item-controls').serializeArray());
        if (validator(item.widget, data)) {
            util.post("/shop/cart/add", data, res => {
                message.alert("商品已加入购物车", "success");
            });
        }
    });

    $buyNow.click(function () {
        let data = util.arrayToObject($('.item-controls').serializeArray());
        if (validator(item.widget, data)) {
            util.post("/shop/order/trade", {
                items: [
                    data
                ]
            }, res => {
                $buyNow.attr("disabled", true);
                window.location.href = "/checkout?tradeNo=" + res.data.trade_no;
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
    });

    let skuWholesaleIndex;
    $skuWholesale.hover(function () {
        const skuId = $skuId.val();
        item?.sku?.forEach(sku => {
            if (skuId == sku.id && sku?.have_wholesale == true) {
                let html = ``;
                sku?.wholesale?.forEach(wholesale => {
                    html += `购买数量达到 <b class="fw-bold fs-4">${wholesale.quantity}</b> 件以上，优惠价：<b class="fw-bold fs-6" style="color: #adff34;">${getVar("CCY")}${wholesale.price}</b><br>`;
                });
                skuWholesaleIndex = layer.tips(i18n(html), this, {
                    tips: [1, '#ee6969'],
                    time: 0,
                    area: ['320px', 'auto']
                });
            }
        });
    }, function () {
        layer.close(skuWholesaleIndex);
    });


    updateItemAmount(getPrice($skuId.val(), $quantity.val()), $quantity.val());//初始化金额


    $itemImageMain.on('click', function () {
        let originalHeight = $(this).css('height');
        if ($(this).data('isExpanded')) {
            // 如果已经展开，收回去
            $(this).animate({height: $(this).data('originalHeight')}, 150);
            $(this).data('isExpanded', false);
        } else {
            // 记录原高度并展开
            $(this).data('originalHeight', originalHeight);
            $(this).css('height', 'auto');
            let newHeight = $(this).height();
            $(this).css('height', originalHeight);
            $(this).animate({height: newHeight}, 150);
            $(this).data('isExpanded', true);
        }
    });
}();