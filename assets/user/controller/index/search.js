!function () {

    let $searchOrderBtn = $(".search-order-btn");
    let $searchOrderKeyword = $(".search-order-keyword");
    let $btnTreasureShow = $(".btn-treasure-show");
    let $btnTreasureCopy = $(".btn-treasure-copy");
    let $btnTreasureDownload = $(".btn-treasure-download");

    $searchOrderBtn.click(() => {
        let tradeNo = $searchOrderKeyword.val();
        if (!(/^\d{24}$/.test(tradeNo))) {
            message.warning("订单号输入错误");
            return false;
        }
        window.location.href = "/search?tradeNo=" + tradeNo;
    });


    $searchOrderKeyword.on('keypress', function (e) {
        if (e.which === 13) {
            $searchOrderBtn.click();
        }
    });

    $btnTreasureShow.click(function () {
        const tradeNo = $(this).parent().attr("data-tradeNo"), itemId = parseInt($(this).parent().attr("data-id"));
        util.post("/shop/order/getOrder", {trade_no: tradeNo, item_id: itemId}, res => {
            treasure.show(res.data);
        });
    });

    $btnTreasureCopy.click(function () {
        const tradeNo = $(this).parent().attr("data-tradeNo"), itemId = parseInt($(this).parent().attr("data-id"));
        util.post("/shop/order/getOrder", {trade_no: tradeNo, item_id: itemId}, res => {
            util.copyTextToClipboard(res.data.treasure, () => {
                message.success("复制成功！");
            }, () => {
                message.success("复制失败，请手动复制！");
            });
        });
    });

    $btnTreasureDownload.click(function () {
        const tradeNo = $(this).parent().attr("data-tradeNo"), itemId = parseInt($(this).parent().attr("data-id"));
        window.open(`/shop/order/download?tradeNo=${tradeNo}&itemId=${itemId}`);
    });
}();