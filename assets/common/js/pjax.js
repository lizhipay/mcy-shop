!function () {
    if (util.isPc()) {
        $(document).pjax('a[target!=_blank]', '#main-container', {fragment: '#main-container', timeout: 8000});
        $(document).on('pjax:send', function () {
            $(".loading").css("display", "block");
        });
        $(document).on('pjax:complete', function () {
            $(".loading").css("display", "none");
        });
        $("a[target!=_blank]").click(function () {
            $('a[target!=_blank]').removeClass("active");
            $(this).addClass("active");
        });
    }
}();