define(
    [
        'Magento_Checkout/js/view/billing-address',
        'Magento_Checkout/js/model/quote',
        'uiRegistry',
        'underscore',
        'jquery'
    ],
    function (Component,
              quote,
              registry,
              _,
              jQuery
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amasty_Checkout/billing-address'
            },

            useShippingAddress: function () {
                registry.get('checkout').disableOrderActionAllowed();
                return this._super();
            },

            editAddress: function () {
                registry.get('checkout').disableOrderActionAllowed();
                return this._super();
            },

            updateAddresses: function () {
                if (quote.billingAddress()) {
                    registry.get('checkout').enableOrderActionAllowed();
                }
                return this._super();
            },

            cancelAddressEdit: function () {
                registry.get('checkout').enableOrderActionAllowed();
                return this._super();
            },

            /**
             * Get code
             * @param {Object} parent
             * @returns {String}
             */
            getCode: function (parentObject) {
                jQuery('.payment-method-billing-address').hide();
                var billingAddressElement = jQuery('.payment-method._active').find('.payment-method-billing-address');
                billingAddressElement.appendTo(jQuery('.payment-method._active').closest('.payment-method'));
                jQuery('.payment-method._active').closest('.payment-method').find('.payment-method-billing-address').show();

                //this condition for minor version Magento 2.x.x
                //the minor verios doesn't have method getCode in
                // module-checkout/view/frontend/web/js/view/billing-address.js
                if (_.isFunction(parentObject.getCode)) {
                    return parentObject.getCode();
                }
                return this._super(parentObject);
            }
        });
    }
);
