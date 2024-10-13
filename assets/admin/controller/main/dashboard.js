!function () {

    function handleProgressShopOrder() {

        const table = new Table("/admin/shop/order/get", "#shop-progress-order-table");
        table.setPagination(6, []);
        table.setColumns([
            {
                field: 'trade_no', title: '订单号'
            },
            {field: 'type', title: '订单类型', dict: "shop_order_type"},
            {
                field: 'total_amount', title: '订单金额', formatter: (amount, item) => {
                    return format.money(amount, "#19bf5d");
                }
            },
            {field: 'customer', title: '会员', formatter: format.customer},
            {field: 'create_time', title: '下单时间'},
            {field: 'create_ip', title: 'IP地址'},
            {field: 'create_browser', title: '浏览器'},
            {field: 'create_device', title: '设备'},
        ]);
        table.setWhere("equal-status", 3);
        table.onResponse(data => {
            $('.data-count .order-count').html(data.data.order_count);
            $('.data-count .order-amount').html(data.data.order_amount);
        });
        table.render();

    }

    function handleStatistics(date = 0, loader = false) {
        util.post({
            url: "/admin/dashboard/statistics",
            loader: loader,
            data: {date: date},
            done: res => {
                $(".shipment-amount").html(getVar("CCY") + res.data.shipment_amount);
                $(".shipment-count").html(res.data.shipment_count);
                $(".shipment-profit").html(getVar("CCY") + res.data.shipment_profit);
                $(".recharge-amount").html(getVar("CCY") + res.data.recharge_amount);
                $(".recharge-count").html(res.data.recharge_count);
                $(".pay-trade-amount").html(getVar("CCY") + res.data.pay_trade_amount);
                $(".pay-balance-amount").html(getVar("CCY") + res.data.pay_balance_amount);
                $(".pay-count").html(res.data.pay_count);
                $(".user-register-count").html(res.data.user_register_count);
                $(".user-active-count").html(res.data.user_active_count);
                $(".user-new-merchant-count").html(res.data.user_new_merchant_count);
                $(".withdraw-amount").html(getVar("CCY") + res.data.withdraw_amount);
            }
        });
    }


    function handleLoadNotice() {
        util.post({
            url: "/admin/store/notice",
            loader: false,
            done: res => {
                res.data.forEach(item => {
                    $('.store-notice').append(`<div class="d-flex align-items-center position-relative mb-3">
                            <!--begin::Label-->
                            <div class="position-absolute top-0 start-0 rounded h-100 bg-light bg-secondary" style="width: 4px;"></div>
                            <!--end::Label-->
                            <!--begin::Details-->
                            <div class="fw-bold ms-3">
                                <a href="${item.url ?? 'javascript:void(0);'}" ${item.url ? 'target="_blank"' : ''} class="fs-7 fw-bolder" style="color: #b593a4;">${item.content}</a>
                                <div class="text-muted my-1 fw-normal">${item.create_time}</div>
                                <!--end::Info-->
                            </div>
                            <!--end::Details-->
                        </div>`);
                });
            }
        });
    }


    $(".select-show-date").change(function () {
        handleStatistics($(this).val(), true);
    });


    handleStatistics();
    handleProgressShopOrder();
    handleLoadNotice();
}();