var Calculator = (function ($) {
    var result = {};


    var ensureNovaPoshta = function () {
        var currentShippingMethod = $('input[name^=shipping_method][type=radio]:checked');
        return currentShippingMethod.val() === 'nova_poshta_shipping_method';
    };

    var addNovaPoshtaHandlers = function () {
        //if not ukraine, disable form else enable form
        //hide region input, instead print nova_poshta_region select,
        //hide city input, instead print nova_poshta_city select,
    };

    var initCalculatorOptionsHandlers = function () {
        $(document.body).bind('updated_shipping_method', function () {
            if (ensureNovaPoshta()) {
                addNovaPoshtaHandlers();
            }
        });
        if (ensureNovaPoshta()) {
            addNovaPoshtaHandlers();
        }
    };

    result.init = function () {
        initCalculatorOptionsHandlers();
    };

    return result;
}(jQuery));
