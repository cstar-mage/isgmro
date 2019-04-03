define(
    [
        'mage/storage',
        'Amasty_Checkout/js/model/resource-url-manager',
        'jquery',
        'uiComponent',
        'ko',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'Amasty_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/modal/alert',
        'mage/translate'
    ],
    function (
        storage,
        resourceUrlManager,
        $,
        Component,
        ko,
        registry,
        quote,
        setShippingInformationAction,
        additionalValidators,
        alert,
        $t
    ) {
        'use strict';

        var paymentsWithRedirect = ['paypal_express', 'paypal_express_bml', 'braintree_paypal', 'braintree'];

        return Component.extend({
            isPlaceOrderActionAllowed: ko.observable(true),

            enableOrderActionAllowed: function () {
                this.isPlaceOrderActionAllowed(true);
            },

            disableOrderActionAllowed: function () {
                this.isPlaceOrderActionAllowed(false);
            },

            requestComponent: function (name) {
                var observable = ko.observable();

                registry.get(name, function (summary) {
                    observable(summary);
                });
                return observable;
            },

            placeOrder: function () {
                var paymentMethod = quote.getPaymentMethod()();
                var methodCode = paymentMethod ? paymentMethod.method : false;

                if (methodCode == "amazon_payment") {
                    var billingStreet = quote.billingAddress().street;
                    var shippingStreet = quote.shippingAddress().street;

                    if (!shippingStreet.length
                        || (shippingStreet.length == 1 && !shippingStreet[0].length)) {
                        quote.shippingAddress().street = billingStreet;
                    }
                }

                if (!methodCode) {
                    alert({content: $t('No payment method selected')});
                    return;
                }

                var methodComponent = registry.get('checkout.steps.billing-step.payment.payments-list.' + methodCode);

                if (methodComponent
                    && methodComponent.hasOwnProperty('isReviewRequired')
                    && !methodComponent.isReviewRequired()
                ) {
                    $('.payment-method._active .actions-toolbar:not([style*="display: none"]) button[type=submit]').click();
                    return;
                }
                var commentValid = true;
                var orderCommentValue = jQuery('#comment-code').val();
                var commentLength = orderCommentValue.length;
                if(commentLength > 250){
                    var validationFailedBlock = '<div id="comment-code-validation-block" for="comment-code" generated="true" class="mage-error" id="email-error">Maximum length: 250 characters</div>';
                    if(jQuery('#comment-code-validation-block').length == 0){
                        jQuery(validationFailedBlock).insertAfter('#comment-code');
                    }
                    jQuery('#comment-code').addClass('mage-error');
                    commentValid = false;
                } else {
                    if(jQuery('#comment-code-validation-block').length){
                        jQuery('#comment-code-validation-block').remove();
                    }
                    jQuery('#comment-code').removeClass('mage-error');
                }
                if(!commentValid){
                    return;
                }
                //Amasty_Deliverydate validation
                var amastyDeliveryDate = registry.get('checkout.steps.shipping-step.shippingAddress.shippingAdditional.amasty-delivery-date');
                if (amastyDeliveryDate && amastyDeliveryDate.__proto__.hasOwnProperty('validate')) {
                    if (!amastyDeliveryDate.validate()) {
                        this._focusFirstErrorField();
                        return false;
                    }
                }

                //Amasty_Checkout develivery date validation
                var amastyCheckoutDeliveryDate = registry.get('checkout.steps.shipping-step.amcheckout-delivery-date');
                if (amastyCheckoutDeliveryDate && amastyCheckoutDeliveryDate.__proto__.hasOwnProperty('validate')) {
                    if (!amastyCheckoutDeliveryDate.validate()) {
                        this._focusFirstErrorField();
                        return false;
                    }
                }

                if (!additionalValidators.validate()) {
                    this._focusFirstErrorField();
                    return false;
                }

                if (quote.isVirtual()) {
                       this._savePaymentAndPlaceOrder();
                }
                else {
                    var shippingAddress = registry.get('checkout.steps.shipping-step.shippingAddress');
                    if (methodCode == "amazon_payment" || shippingAddress.validateShippingInformation()) {
                        setShippingInformationAction().done(this._savePaymentAndPlaceOrder);
                    } else {
                        this._focusFirstErrorField();
                    }
                }
            },

            _savePaymentAndPlaceOrder: function () {
                var paymentMethodCode = quote.getPaymentMethod()().method;
                if (paymentsWithRedirect.indexOf(paymentMethodCode) !== -1) {
                    var serviceUrl = resourceUrlManager.getUrlForInitNewsletter(quote);
                    var payload    = {
                        cartId: quote.getQuoteId(),
                        email:  quote.guestEmail,
                    };

                    var amcheckoutForm = $('.additional-options input, .additional-options textarea');
                    var amcheckoutFormData = amcheckoutForm.serializeArray();

                    var data = {};
                    amcheckoutFormData.forEach(function(item){
                        data[item.name] = item.value;
                    });
                    jQuery('[name="comment-code"]').each(function(index, element){
                        var orderCommentValue = jQuery(element).val();
                         if(orderCommentValue && orderCommentValue.length > 0){
                             data['comment-code'] = orderCommentValue;
                         }
                    });
                    payload.amcheckoutData = data;

                    storage.post(serviceUrl, JSON.stringify(payload), false);
                }
                $('.payment-method._active button[type=submit]').click();
            },

            _focusFirstErrorField: function() {
                var errorField = $('.mage-error:visible:first');
                if (errorField.prop('tagName') && errorField.prop('tagName').toLowerCase() == 'input') {
                    errorField.focus();
                } else if (errorField.prop('tagName') && errorField.prop('tagName').toLowerCase() == 'div') {
                    errorField.prevAll(':input').eq(0).focus();
                }
            }
        });
    }
);
