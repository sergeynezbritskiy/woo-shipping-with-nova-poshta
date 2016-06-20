jQuery(document).ready(function ($) {

    var NovaPoshta = {

        novaPoshtaBillingOptions: null,
        billingAreaSelect: null,
        billingCitySelect: null,
        billingWarehouseSelect: null,

        novaPoshtaShippingOptions: null,
        shippingAreaSelect: null,
        shippingCitySelect: null,
        shippingWarehouseSelect: null,

        shippingMethod: null,

        defaultBillingOptions: null,
        defaultShippingOptions: null,

        shipToDifferentAddress: function () {
            return $('#ship-to-different-address-checkbox').is(':checked');
        },

        init: function () {

            this.novaPoshtaBillingOptions = $('#billing_nova_poshta_region, #billing_nova_poshta_city, #billing_nova_poshta_warehouse');
            this.billingAreaSelect = $('#billing_nova_poshta_region');
            this.billingCitySelect = $('#billing_nova_poshta_city');
            this.billingWarehouseSelect = $('#billing_nova_poshta_warehouse');

            this.novaPoshtaShippingOptions = $('#shipping_nova_poshta_region, #shipping_nova_poshta_city, #shipping_nova_poshta_warehouse');
            this.shippingAreaSelect = $('#shipping_nova_poshta_region');
            this.shippingCitySelect = $('#shipping_nova_poshta_city');
            this.shippingWarehouseSelect = $('#shipping_nova_poshta_warehouse');

            this.shippingMethod = $("input[name^=shipping_method][type=radio]");

            this.defaultBillingOptions = $('#billing_address_1, #billing_address_2, #billing_city, #billing_state, #billing_postcode');
            this.defaultShippingOptions = $('#shipping_address_1, #shipping_address_2, #shipping_city, #shipping_state, #shipping_postcode');

            $(document).on('change', NovaPoshta.shippingMethod, function () {
                NovaPoshta.handleShippingMethodChange();
            });

            $(document).on('change', '#ship-to-different-address-checkbox', function () {
                NovaPoshta.handleShippingMethodChange();
            });

            this.billingAreaSelect.on('change', function () {
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
                            NovaPoshta.billingCitySelect
                                .find('option')
                                .remove();

                            $.each(data, function (key, value) {
                                NovaPoshta.billingCitySelect
                                    .append($("<option></option>")
                                        .attr("value", key)
                                        .text(value)
                                    );
                            });
                            NovaPoshta.billingWarehouseSelect.find('option').remove();

                        } catch (s) {
                            console.log("Error. Response from server was: " + json);
                        }
                    },
                    error: function () {
                        console.log('Error.');
                    }
                });
            });

            this.billingCitySelect.on('change', function () {
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
                            NovaPoshta.billingWarehouseSelect
                                .find('option')
                                .remove();

                            $.each(data, function (key, value) {
                                NovaPoshta.billingWarehouseSelect
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

            this.shippingAreaSelect.on('change', function () {
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
                            NovaPoshta.shippingCitySelect
                                .find('option')
                                .remove();

                            $.each(data, function (key, value) {
                                NovaPoshta.shippingCitySelect
                                    .append($("<option></option>")
                                        .attr("value", key)
                                        .text(value)
                                    );
                            });
                            NovaPoshta.shippingWarehouseSelect.find('option').remove();

                        } catch (s) {
                            console.log("Error. Response from server was: " + json);
                        }
                    },
                    error: function () {
                        console.log('Error.');
                    }
                });
            });

            this.shippingCitySelect.on('change', function () {
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
                            NovaPoshta.shippingWarehouseSelect
                                .find('option')
                                .remove();

                            $.each(data, function (key, value) {
                                NovaPoshta.shippingWarehouseSelect
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

            this.handleShippingMethodChange();
        },

        /**
         * @returns {boolean}
         */
        ensureNovaPoshta: function () {
            var currentShippingMethod = $('input[name^=shipping_method][type=radio]:checked');
            return currentShippingMethod.val() === 'nova_poshta_shipping_method';
        },

        //billing
        enableNovaPoshtaBillingOptions: function () {
            NovaPoshta.novaPoshtaBillingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
            NovaPoshta.disableDefaultBillingOptions();
        },

        disableNovaPoshtaBillingOptions: function () {
            NovaPoshta.novaPoshtaBillingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
            NovaPoshta.enableDefaultBillingOptions();
        },

        enableDefaultBillingOptions: function () {
            NovaPoshta.defaultBillingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
        },

        disableDefaultBillingOptions: function () {
            NovaPoshta.defaultBillingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
        },

        //shipping
        enableNovaPoshtaShippingOptions: function () {
            NovaPoshta.novaPoshtaShippingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
            NovaPoshta.disableDefaultShippingOptions();
        },

        disableNovaPoshtaShippingOptions: function () {
            NovaPoshta.novaPoshtaShippingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
            NovaPoshta.enableDefaultShippingOptions();
        },

        enableDefaultShippingOptions: function () {
            NovaPoshta.defaultShippingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
        },

        disableDefaultShippingOptions: function () {
            NovaPoshta.defaultShippingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
        },

        //total
        // disableDefaultOptions: function () {
        //     NovaPoshta.disableDefaultShippingOptions();
        //     NovaPoshta.disableDefaultBillingOptions();
        // },

        // enableDefaultOptions: function () {
        //     NovaPoshta.enableDefaultBillingOptions();
        //     NovaPoshta.enableDefaultShippingOptions();
        // },

        disableNovaPoshtaOptions: function () {
            NovaPoshta.disableNovaPoshtaBillingOptions();
            NovaPoshta.disableNovaPoshtaShippingOptions();
        },

        //other
        handleShippingMethodChange: function () {
            NovaPoshta.disableNovaPoshtaOptions();
            if (NovaPoshta.ensureNovaPoshta()) {
                if (NovaPoshta.shipToDifferentAddress()) {
                    NovaPoshta.enableNovaPoshtaShippingOptions();
                } else {
                    NovaPoshta.enableNovaPoshtaBillingOptions();
                }
            }
        }
    };

    NovaPoshta.init();
});