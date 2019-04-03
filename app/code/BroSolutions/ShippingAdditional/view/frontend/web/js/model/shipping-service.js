/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Checkout/js/model/checkout-data-resolver'
], function (ko, checkoutDataResolver) {
    'use strict';

    var shippingRates = ko.observableArray([]);

    return {
        isLoading: ko.observable(false),

        /**
         * Set shipping rates
         *
         * @param {*} ratesData
         */
        setShippingRates: function (ratesData) {
            var ratesDataSorted = [];
            var shippingAdditionalMethodData = false;

            jQuery.each(ratesData, function(index, element){
                if(element.carrier_code != 'undefined' && element.carrier_code == 'shippingadditional'){
                    shippingAdditionalMethodData = element;
                } else {
                    ratesDataSorted.push(element);
                }
            });
            if(shippingAdditionalMethodData){
                ratesDataSorted.push(shippingAdditionalMethodData);
            }
            shippingRates(ratesDataSorted);
            shippingRates.valueHasMutated();
            checkoutDataResolver.resolveShippingRates(ratesData);
        },

        /**
         * Get shipping rates
         *
         * @returns {*}
         */
        getShippingRates: function () {
            return shippingRates;
        }
    };
});
