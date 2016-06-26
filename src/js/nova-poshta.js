jQuery(document).ready(function () {
    var NovaPoshta = (function ($) {

        var my = {};
        var novaPoshtaBillingOptions = $('#billing_nova_poshta_region, #billing_nova_poshta_city, #billing_nova_poshta_warehouse');
        var billingAreaSelect = $('#billing_nova_poshta_region');
        var billingCitySelect = $('#billing_nova_poshta_city');
        var billingWarehouseSelect = $('#billing_nova_poshta_warehouse');

        var novaPoshtaShippingOptions = $('#shipping_nova_poshta_region, #shipping_nova_poshta_city, #shipping_nova_poshta_warehouse');
        var shippingAreaSelect = $('#shipping_nova_poshta_region');
        var shippingCitySelect = $('#shipping_nova_poshta_city');
        var shippingWarehouseSelect = $('#shipping_nova_poshta_warehouse');

        // var shippingMethod = $("input[name^=shipping_method][type=radio]");

        var defaultBillingOptions = $('#billing_address_1, #billing_address_2, #billing_city, #billing_state, #billing_postcode');
        var defaultShippingOptions = $('#shipping_address_1, #shipping_address_2, #shipping_city, #shipping_state, #shipping_postcode');

        function shipToDifferentAddress() {
            return $('#ship-to-different-address-checkbox').is(':checked');
        }

        function ensureNovaPoshta() {
            var currentShippingMethod = $('input[name^=shipping_method][type=radio]:checked');
            return currentShippingMethod.val() === 'nova_poshta_shipping_method';
        }

        //billing
        function enableNovaPoshtaBillingOptions() {
            novaPoshtaBillingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
            disableDefaultBillingOptions();
        }

        function disableNovaPoshtaBillingOptions() {
            novaPoshtaBillingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
            enableDefaultBillingOptions();
        }

        function enableDefaultBillingOptions() {
            defaultBillingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
        }

        function disableDefaultBillingOptions() {
            defaultBillingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
        }

        //shipping
        function enableNovaPoshtaShippingOptions() {
            novaPoshtaShippingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
            disableDefaultShippingOptions();
        }

        function disableNovaPoshtaShippingOptions() {
            novaPoshtaShippingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
            enableDefaultShippingOptions();
        }

        function enableDefaultShippingOptions() {
            defaultShippingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
        }

        function disableDefaultShippingOptions() {
            defaultShippingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
        }

        function disableNovaPoshtaOptions() {
            disableNovaPoshtaBillingOptions();
            disableNovaPoshtaShippingOptions();
        }

        //other
        function handleShippingMethodChange() {
            disableNovaPoshtaOptions();
            if (ensureNovaPoshta()) {
                if (shipToDifferentAddress()) {
                    enableNovaPoshtaShippingOptions();
                } else {
                    enableNovaPoshtaBillingOptions();
                }
            }
        }

        function initHandlers() {
            $(document).on('change', "input[name^=shipping_method][type=radio]", function () {
                handleShippingMethodChange();
            });

            $(document).on('change', '#ship-to-different-address-checkbox', function () {
                handleShippingMethodChange();
            });
            handleShippingMethodChange();
        }

        function initNovaPoshtaOptions() {
            billingAreaSelect.on('change', function () {
                var areaRef = this.value;
                $.ajax({
                    url: NovaPoshtaHelper.ajaxUrl,
                    method: "POST",
                    data: {
                        'action': NovaPoshtaHelper.getCitiesAction,
                        'parent_area_ref': areaRef
                    },
                    success: function (json) {
                        try {
                            var data = JSON.parse(json);
                            billingCitySelect.find('option').remove();

                            $.each(data, function (key, value) {
                                billingCitySelect.append($("<option></option>").attr("value", key).text(value));
                            });
                            billingWarehouseSelect.find('option').remove();

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
                        'parent_area_ref': cityRef
                    },
                    success: function (json) {
                        try {
                            var data = JSON.parse(json);
                            billingWarehouseSelect
                                .find('option')
                                .remove();

                            $.each(data, function (key, value) {
                                billingWarehouseSelect.append($("<option></option>").attr("value", key).text(value));
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
                        'parent_area_ref': areaRef
                    },
                    success: function (json) {
                        try {
                            var data = JSON.parse(json);
                            shippingCitySelect
                                .find('option')
                                .remove();

                            $.each(data, function (key, value) {
                                shippingCitySelect
                                    .append($("<option></option>")
                                        .attr("value", key)
                                        .text(value)
                                    );
                            });
                            shippingWarehouseSelect.find('option').remove();

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
                        'parent_area_ref': cityRef
                    },
                    success: function (json) {
                        try {
                            var data = JSON.parse(json);
                            shippingWarehouseSelect
                                .find('option')
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
        }

        my.init = function () {
            initHandlers();
            initNovaPoshtaOptions();
            console.info('Started');
        };

        return my;
    }(jQuery));

    NovaPoshta.init();
});