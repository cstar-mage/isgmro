define([
    'uiComponent',
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote'
], function (Component, jQuery, ko, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'BroSolutions_ShippingAdditional/checkout/shipping/shipping-additional.phtml'
        },
        initObservable: function () {
            this.selectedMethod = ko.computed(function() {
                var method = quote.shippingMethod();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                return selectedMethod;
            }, this);
            var selectOption = function(val, title) {
                this.val = val;
                this.title = title;
            };
            var optionsArray = [];
            jQuery.each(window.checkoutConfig.dropdown_config, function(val, title){
                optionsArray.push(new selectOption(val, title));
            });
            this.availableOptions = ko.observableArray(optionsArray);
            this.selectedOption = function(){

            };
            return this;
        }
    });
});
