define(
    [
        'jquery',
        'Magento_Ui/js/lib/view/utils/async',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/view/shipping',
        'Magento_Checkout/js/model/quote',
        'uiRegistry'
    ],
    function (
        $,
        async,
        setShippingInformationAction,
        Shipping,
        quote,
        registry
    ) {
        'use strict';

        var instance = null;

        // Fix js error in Magento 2.2
        function fixAddress(address) {
            if (!address) {
                return;
            }

            if (Array.isArray(address.street) && address.street.length == 0) {
                address.street = ['', ''];
            }
        }

        function removeAmazonPayButton() {
            var amazonPaymentButton = $('#PayWithAmazon_amazon-pay-button img');
            if (amazonPaymentButton.length > 1) {
                amazonPaymentButton.not(':first').remove();
            }
        }

        return Shipping.extend({
            setShippingInformation: function () {
                fixAddress(quote.shippingAddress());
                fixAddress(quote.billingAddress());

                setShippingInformationAction().done(
                    function () {
                        //stepNavigator.next();
                    }
                );
            },
            initialize: function () {
                this._super();
                instance = this;

                registry.get('checkout.steps.shipping-step.shippingAddress.before-form.amazon-widget-address.before-widget-address.amazon-checkout-revert', 
                    function (component) {
                        component.isAmazonAccountLoggedIn.subscribe(function (loggedIn) {
                            if (!loggedIn) {
                                registry.get('checkout.steps.shipping-step.shippingAddress', function (component) {
                                    if (component.isSelected()) {
                                        component.selectShippingMethod(quote.shippingMethod());
                                    }
                                });
                            }
                        });
                    }
                );

                registry.get('checkout.steps.billing-step.payment.payments-list.amazon_payment', function (component) {
                    if (component.isAmazonAccountLoggedIn()) {
                        $('button.action-show-popup').hide();
                    }
                });

                registry.get('checkout.steps.shipping-step.shippingAddress.customer-email.amazon-button-region.amazon-button', 
                    function (component) {
                        async.async({
                            selector: "#PayWithAmazon_amazon-pay-button img"
                        }, function () {
                            removeAmazonPayButton();
                        });

                        component.isAmazonAccountLoggedIn.subscribe(function (loggedIn) {
                            if (!loggedIn) {
                                removeAmazonPayButton();
                            }
                        });
                    }
                );
            },

            selectShippingMethod: function (shippingMethod) {
                this._super();

                //instance.setShippingInformation();

                return true;
            }
        });
    }
);
