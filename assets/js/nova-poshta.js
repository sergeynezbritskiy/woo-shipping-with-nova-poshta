jQuery(document).ready(function ($) {

    var NovaPoshta = {

        areaSelect: null,
        citySelect: null,
        warehouseSelect: null,

        init: function () {

            this.areaSelect = $('#woocommerce_nova_poshta_shipping_method_area');
            this.citySelect = $('#woocommerce_nova_poshta_shipping_method_city');
            this.warehouseSelect = $('#woocommerce_nova_poshta_shipping_method_warehouse');

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
        }

    };

    NovaPoshta.init();
});