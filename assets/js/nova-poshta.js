jQuery(document).ready(function ($) {

    var NovaPoshta = {

        areaSelect: null,
        areaInputName: null,
        areaInputKey: null,
        citySelect: null,
        cityInputName: null,
        cityInputKey: null,
        warehouseSelect: null,
        warehouseInputName: null,
        warehouseInputKey: null,

        init: function () {

            this.areaSelect = $('#billing_state');
            this.citySelect = $('#billing_city');
            this.warehouseSelect = $('#billing_address_1');
            this.areaInputName = $('#woocommerce_nova_poshta_shipping_method_area_name');
            this.areaInputKey = $('#woocommerce_nova_poshta_shipping_method_area');
            this.cityInputName = $('#woocommerce_nova_poshta_shipping_method_city_name');
            this.cityInputKey = $('#woocommerce_nova_poshta_shipping_method_city');
            this.warehouseInputName = $('#woocommerce_nova_poshta_shipping_method_warehouse_name');
            this.warehouseInputKey = $('#woocommerce_nova_poshta_shipping_method_warehouse');

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

            this.areaInputName.autocomplete({
                source: function (request, response) {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: NovaPoshtaHelper.getAreasBySuggestionAction,
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
                    NovaPoshta.areaInputName.val(ui.item.label);
                    return false;
                },
                select: function (event, ui) {
                    NovaPoshta.areaInputName.val(ui.item.label);
                    NovaPoshta.areaInputKey.val(ui.item.value);
                    return false;
                }
            });

            this.cityInputName.autocomplete({
                source: function (request, response) {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: NovaPoshtaHelper.getCitiesBySuggestionAction,
                            name: request.term,
                            area_ref: NovaPoshta.areaInputKey.val()
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
                    NovaPoshta.cityInputName.val(ui.item.label);
                    return false;
                },
                select: function (event, ui) {
                    NovaPoshta.cityInputName.val(ui.item.label);
                    NovaPoshta.cityInputKey.val(ui.item.value);
                    return false;
                }
            });

            this.warehouseInputName.autocomplete({
                source: function (request, response) {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: NovaPoshtaHelper.getWarehousesBySuggestionAction,
                            name: request.term,
                            city_ref: NovaPoshta.cityInputKey.val()
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
                    NovaPoshta.warehouseInputName.val(ui.item.label);
                    return false;
                },
                select: function (event, ui) {
                    NovaPoshta.warehouseInputName.val(ui.item.label);
                    NovaPoshta.warehouseInputKey.val(ui.item.value);
                    return false;
                }
            });
        }

    };

    NovaPoshta.init();
});