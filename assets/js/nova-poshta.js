jQuery(document).ready(function ($) {

    var NovaPoshta = {

        areaSelect: null,
        citySelect: null,
        warehouseSelect: null,
        shippingMethod: null,

        init: function () {

            this.areaSelect = $('#nova_poshta_area');
            this.citySelect = $('#nova_poshta_city');
            this.warehouseSelect = $('#nova_poshta_warehouse');
            this.shippingMethod = $("select.shipping_method, input[name^=shipping_method][type=radio], input[name^=shipping_method][type=hidden]");

            // NovaPoshta.handleShippingMethodChange();

            this.shippingMethod.on('change', NovaPoshta.handleShippingMethodChange());

            this.areaSelect.on('change', function () {
                var areaRef = this.value;
                $.ajax({
                    url: NovaPoshtaHelper.ajaxUrl,
                    method: "POST",
                    data: {
                        'action': NovaPoshtaHelper.getCitiesAction,
                        'area_ref': areaRef
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
                        'city_ref': cityRef
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

        },

        /**
         * @returns {boolean}
         */
        ensureNovaPoshta: function () {
            var value = $('input.shipping_method').val();
            console.log(value);
            return (value === 'nova_poshta_shipping_method');
        },

        enableNovaPoshtaOptions: function () {
            //TODO hide all options
            //display nova poshta options
        },

        disableNovaPoshtaOptions: function () {
            //TODO hide nova poshta options
            //display all options
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