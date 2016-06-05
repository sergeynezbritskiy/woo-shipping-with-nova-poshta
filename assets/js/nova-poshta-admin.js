jQuery(document).ready(function ($) {

    var NovaPoshtaAdmin = {

        areaInputName: null,
        areaInputKey: null,
        cityInputName: null,
        cityInputKey: null,
        warehouseInputName: null,
        warehouseInputKey: null,

        init: function () {

            this.areaInputName = $('#woocommerce_nova_poshta_shipping_method_area_name');
            this.areaInputKey = $('#woocommerce_nova_poshta_shipping_method_area');
            this.cityInputName = $('#woocommerce_nova_poshta_shipping_method_city_name');
            this.cityInputKey = $('#woocommerce_nova_poshta_shipping_method_city');
            this.warehouseInputName = $('#woocommerce_nova_poshta_shipping_method_warehouse_name');
            this.warehouseInputKey = $('#woocommerce_nova_poshta_shipping_method_warehouse');

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
                    NovaPoshtaAdmin.areaInputName.val(ui.item.label);
                    return false;
                },
                select: function (event, ui) {
                    NovaPoshtaAdmin.areaInputName.val(ui.item.label);
                    NovaPoshtaAdmin.areaInputKey.val(ui.item.value);
                    NovaPoshtaAdmin.clearCity();
                    NovaPoshtaAdmin.clearWarehouse();
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
                            area_ref: NovaPoshtaAdmin.areaInputKey.val()
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
                    NovaPoshtaAdmin.cityInputName.val(ui.item.label);
                    return false;
                },
                select: function (event, ui) {
                    NovaPoshtaAdmin.cityInputName.val(ui.item.label);
                    NovaPoshtaAdmin.cityInputKey.val(ui.item.value);
                    NovaPoshtaAdmin.clearWarehouse();
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
                            city_ref: NovaPoshtaAdmin.cityInputKey.val()
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
                    NovaPoshtaAdmin.warehouseInputName.val(ui.item.label);
                    return false;
                },
                select: function (event, ui) {
                    NovaPoshtaAdmin.warehouseInputName.val(ui.item.label);
                    NovaPoshtaAdmin.warehouseInputKey.val(ui.item.value);
                    return false;
                }
            });
        },

        clearCity: function () {
            this.cityInputName.val('');
            this.cityInputKey.val('');
        },

        clearWarehouse: function () {
            this.warehouseInputName.val('');
            this.warehouseInputKey.val('');
        }

    };

    NovaPoshtaAdmin.init();

});