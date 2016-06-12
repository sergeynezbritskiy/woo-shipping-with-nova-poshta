jQuery(document).ready(function ($) {

    var NovaPoshta = {

        areaSelect: null,
        citySelect: null,
        warehouseSelect: null,
        shippingMethod: null,
        novaPoshtaOptions: null,
        billingOptions: null,

        init: function () {

            this.areaSelect = $('#nova_poshta_region');
            this.citySelect = $('#nova_poshta_city');
            this.warehouseSelect = $('#nova_poshta_warehouse');
            this.shippingMethod = $("input[name^=shipping_method][type=radio]");
            this.novaPoshtaOptions = $('#nova_poshta_region, #nova_poshta_city, #nova_poshta_warehouse');
            this.billingOptions = $('#billing_address_1, #billing_address_2, #billing_city, #billing_state, #billing_postcode');

            $(document).on('change', NovaPoshta.shippingMethod, function () {
                NovaPoshta.handleShippingMethodChange();
            });

            this.areaSelect.on('change', function () {
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
                            NovaPoshta.citySelect
                                .find('option')
                                .remove();

                            $.each(data, function (key, value) {
                                NovaPoshta.citySelect
                                    .append($("<option></option>")
                                        .attr("value", key)
                                        .text(value)
                                    );
                            });
                            NovaPoshta.warehouseSelect.find('option').remove();

                        } catch (s) {
                            console.log("Error. Response from server was: " + json);
                        }
                    },
                    error: function () {
                        console.log('Error.');
                    }
                });
            });

            this.citySelect.on('change', function () {
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
                            NovaPoshta.warehouseSelect
                                .find('option')
                                .remove();

                            $.each(data, function (key, value) {
                                NovaPoshta.warehouseSelect
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

        enableNovaPoshtaOptions: function () {
            NovaPoshta.novaPoshtaOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
            NovaPoshta.billingOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
        },

        disableNovaPoshtaOptions: function () {
            NovaPoshta.novaPoshtaOptions.each(function () {
                $(this).attr('disabled', 'disabled').closest('.form-row').hide();
            });
            NovaPoshta.billingOptions.each(function () {
                $(this).removeAttr('disabled').closest('.form-row').show();
            });
        },

        handleShippingMethodChange: function () {
            if (NovaPoshta.ensureNovaPoshta()) {
                NovaPoshta.enableNovaPoshtaOptions();
            } else {
                NovaPoshta.disableNovaPoshtaOptions();
            }

        }

    };

    NovaPoshta.init();
});