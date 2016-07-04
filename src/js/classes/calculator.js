var Calculator = (function ($) {
    var result = {};

    var ensureNovaPoshta = function () {
        var currentShippingMethod = $('input[name^=shipping_method][type=radio]:checked');
        return currentShippingMethod.val() === 'nova_poshta_shipping_method';
    };

    var addNovaPoshtaHandlers = function () {
        $('#calc_shipping_country').find('option').each(function () {
            //Ship to Ukraine only
            if ($(this).val() !== 'UA') {
                $(this).remove();
            }
        });
        $('#calc_shipping_state_field').hide();

        var shippingMethod = $('<input type="hidden" id="calc_nova_poshta_shipping_method" value="nova_poshta_shipping_method" name="shipping_method">');
        var cityInputKey = $('<input type="hidden" id="calc_nova_poshta_shipping_city" name="calc_nova_poshta_shipping_city">');
        $('#calc_shipping_city_field').append(cityInputKey).append(shippingMethod);
        var cityInputName = $('#calc_shipping_city');


        cityInputName.autocomplete({
            source: function (request, response) {
                jQuery.ajax({
                    type: 'POST',
                    url: NovaPoshtaHelper.ajaxUrl,
                    data: {
                        action: NovaPoshtaHelper.getCitiesByNameSuggestionAction,
                        name: request.term
                    },
                    success: function (json) {
                        var data = JSON.parse(json);
                        response(jQuery.map(data, function (item) {
                            return {
                                label: item.description,
                                value: item.ref
                            }
                        }));
                    }
                })
            },
            focus: function (event, ui) {
                cityInputName.val(ui.item.label);
                return false;
            },
            select: function (event, ui) {
                cityInputName.val(ui.item.label);
                cityInputKey.val(ui.item.value);
                return false;
            }
        });

        $('form.woocommerce-shipping-calculator').on('submit', function () {
            if ($('#calc_shipping_country').val() !== 'UA') {
                return false;
            }
        });
    };

    result.init = function () {
        $(document.body).bind('updated_shipping_method', function () {
            if (ensureNovaPoshta()) {
                addNovaPoshtaHandlers();
            }
        });
        if (ensureNovaPoshta()) {
            addNovaPoshtaHandlers();
        }
    };

    return result;
}(jQuery));