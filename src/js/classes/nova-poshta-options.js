var NovaPoshtaOptions = (function ($) {
    var result = {};

    var novaPoshtaBillingOptions = $('#billing_nova_poshta_region, #billing_nova_poshta_city, #billing_nova_poshta_warehouse');
    var billingAreaSelect = $('#billing_nova_poshta_region');
    var billingCitySelect = $('#billing_nova_poshta_city');
    var billingWarehouseSelect = $('#billing_nova_poshta_warehouse');

    var novaPoshtaShippingOptions = $('#shipping_nova_poshta_region, #shipping_nova_poshta_city, #shipping_nova_poshta_warehouse');
    var shippingAreaSelect = $('#shipping_nova_poshta_region');
    var shippingCitySelect = $('#shipping_nova_poshta_city');
    var shippingWarehouseSelect = $('#shipping_nova_poshta_warehouse');

    var defaultBillingOptions = $('#billing_address_1, #billing_address_2, #billing_city, #billing_state, #billing_postcode');
    var defaultShippingOptions = $('#shipping_address_1, #shipping_address_2, #shipping_city, #shipping_state, #shipping_postcode');

    var shippingMethod = $("input[name^=shipping_method][type=radio]");
    var shipToDifferentAddressCheckbox = $('#ship-to-different-address-checkbox');

    var shipToDifferentAddress = function () {
        return shipToDifferentAddressCheckbox.is(':checked');
    };

    var ensureNovaPoshta = function () {
        //TODO this method should be more abstract
        var value = $('input[name^=shipping_method][type=radio]:checked').val();
        if (!value) {
            value = $('input#shipping_method_0').val();
        }
        return value === 'nova_poshta_shipping_method';
    };

    //billing
    var enableNovaPoshtaBillingOptions = function () {
        novaPoshtaBillingOptions.each(function () {
            $(this).removeAttr('disabled').closest('.form-row').show();
        });
        disableDefaultBillingOptions();
    };

    var disableNovaPoshtaBillingOptions = function () {
        novaPoshtaBillingOptions.each(function () {
            $(this).attr('disabled', 'disabled').closest('.form-row').hide();
        });
        enableDefaultBillingOptions();
    };

    var enableDefaultBillingOptions = function () {
        defaultBillingOptions.each(function () {
            $(this).removeAttr('disabled').closest('.form-row').show();
        });
    };

    var disableDefaultBillingOptions = function () {
        defaultBillingOptions.each(function () {
            $(this).attr('disabled', 'disabled').closest('.form-row').hide();
        });
    };

    //shipping
    var enableNovaPoshtaShippingOptions = function () {
        novaPoshtaShippingOptions.each(function () {
            $(this).removeAttr('disabled').closest('.form-row').show();
        });
        disableDefaultShippingOptions();
    };

    var disableNovaPoshtaShippingOptions = function () {
        novaPoshtaShippingOptions.each(function () {
            $(this).attr('disabled', 'disabled').closest('.form-row').hide();
        });
        enableDefaultShippingOptions();
    };

    var enableDefaultShippingOptions = function () {
        defaultShippingOptions.each(function () {
            $(this).removeAttr('disabled').closest('.form-row').show();
        });
    };

    var disableDefaultShippingOptions = function () {
        defaultShippingOptions.each(function () {
            $(this).attr('disabled', 'disabled').closest('.form-row').hide();
        });
    };

    //common
    var disableNovaPoshtaOptions = function () {
        disableNovaPoshtaBillingOptions();
        disableNovaPoshtaShippingOptions();
    };

    var handleShippingMethodChange = function () {
        disableNovaPoshtaOptions();
        if (ensureNovaPoshta()) {
            if (shipToDifferentAddress()) {
                enableNovaPoshtaShippingOptions();
            } else {
                enableNovaPoshtaBillingOptions();
            }
        }
    };

    var initShippingMethodHandlers = function () {
        //TODO check count of call of this method during initialisation and other actions
        $(document).on('change', shippingMethod, function () {
            handleShippingMethodChange();
        });
        $(document).on('change', shipToDifferentAddressCheckbox, function () {
            handleShippingMethodChange();
        });
        $(document.body).bind('updated_checkout', function () {
            handleShippingMethodChange();
        });
        handleShippingMethodChange();
    };

    var initOptionsHandlers = function () {
        billingAreaSelect.on('change', function () {
            var areaRef = this.value;
            $.ajax({
                url: NovaPoshtaHelper.ajaxUrl,
                method: "POST",
                data: {
                    'action': NovaPoshtaHelper.getCitiesAction,
                    'parent_ref': areaRef
                },
                success: function (json) {
                    try {
                        var data = JSON.parse(json);
                        billingCitySelect
                            .find('option:not(:first-child)')
                            .remove();

                        $.each(data, function (key, value) {
                            billingCitySelect
                                .append($("<option></option>")
                                    .attr("value", key)
                                    .text(value)
                                );
                        });
                        billingWarehouseSelect.find('option:not(:first-child)').remove();

                    } catch (s) {
                        console.log("Error. Response from server was: " + json);
                    }
                },
                error: function () {
                    console.log('Error.');
                }
            });
        });
        billingCitySelect.on('change', function () {
            var cityRef = this.value;
            $.ajax({
                url: NovaPoshtaHelper.ajaxUrl,
                method: "POST",
                data: {
                    'action': NovaPoshtaHelper.getWarehousesAction,
                    'parent_ref': cityRef
                },
                success: function (json) {
                    try {
                        var data = JSON.parse(json);
                        billingWarehouseSelect
                            .find('option:not(:first-child)')
                            .remove();

                        $.each(data, function (key, value) {
                            billingWarehouseSelect
                                .append($("<option></option>")
                                    .attr("value", key)
                                    .text(value)
                                );
                        });

                    } catch (s) {
                        console.log("Error. Response from server was: " + json);
                    }
                },
                error: function () {
                    console.log('Error.');
                }
            });
        });
        shippingAreaSelect.on('change', function () {
            var areaRef = this.value;
            $.ajax({
                url: NovaPoshtaHelper.ajaxUrl,
                method: "POST",
                data: {
                    'action': NovaPoshtaHelper.getCitiesAction,
                    'parent_ref': areaRef
                },
                success: function (json) {
                    try {
                        var data = JSON.parse(json);
                        shippingCitySelect
                            .find('option:not(:first-child)')
                            .remove();

                        $.each(data, function (key, value) {
                            shippingCitySelect
                                .append($("<option></option>")
                                    .attr("value", key)
                                    .text(value)
                                );
                        });
                        shippingWarehouseSelect.find('option:not(:first-child)').remove();

                    } catch (s) {
                        console.log("Error. Response from server was: " + json);
                    }
                },
                error: function () {
                    console.log('Error.');
                }
            });
        });
        shippingCitySelect.on('change', function () {
            var cityRef = this.value;
            $.ajax({
                url: NovaPoshtaHelper.ajaxUrl,
                method: "POST",
                data: {
                    'action': NovaPoshtaHelper.getWarehousesAction,
                    'parent_ref': cityRef
                },
                success: function (json) {
                    try {
                        var data = JSON.parse(json);
                        shippingWarehouseSelect
                            .find('option:not(:first-child)')
                            .remove();

                        $.each(data, function (key, value) {
                            shippingWarehouseSelect
                                .append($("<option></option>")
                                    .attr("value", key)
                                    .text(value)
                                );
                        });

                    } catch (s) {
                        console.log("Error. Response from server was: " + json);
                    }
                },
                error: function () {
                    console.log('Error.');
                }
            });
        });
    };

    result.init = function () {
        initShippingMethodHandlers();
        initOptionsHandlers();
    };

    return result;
}(jQuery));
