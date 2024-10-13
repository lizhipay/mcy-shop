!function () {
    $(".language-select").select2({
        templateResult: format.select2BankCard,
        templateSelection: format.select2BankCard,
        theme: 'bootstrap-5 language-select-theme',
        minimumResultsForSearch: Infinity
    }).change(function () {
        language.change($(this).val());
    });

}();