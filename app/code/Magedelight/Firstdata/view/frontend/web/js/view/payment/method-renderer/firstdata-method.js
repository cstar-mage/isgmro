define(
    [
        'underscore',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/quote',
        'mage/translate',
        'Magento_Checkout/js/model/full-screen-loader',
         'ko',
         'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
    ],
    function (
        _,
        $,
        Component,
        quote,
       $t,
       fullScreenLoader,
        ko,
        creditCardData, 
        cardNumberValidator
    ) {
        'use strict';
        var configFirstdata = window.checkoutConfig.payment.md_firstdata;
        
        
        return Component.extend({
            
            defaults: {
                template: 'Magedelight_Firstdata/payment/firstdata',
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardSsIssue: '',
                creditCardVerificationNumber: '',
                selectedCardType: null
               
            },
            newCardVisible: ko.observable(false),
            newCardVisibleSave: ko.observable(false),
            savechecked: ko.observable(true),
            noSavedCardAvail: ko.observable(false),
             /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                
                 this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'creditCardSsIssue',
                        'selectedCardType'
                    ]);
                    return this;
            },
           
           initialize: function () {
                var self = this;

                this._super();

                //Set credit card number to credit card data object
                this.creditCardNumber.subscribe(function (value) {
                    var result;

                    self.selectedCardType(null);

                    if (value === '' || value === null) {
                        return false;
                    }
                    result = cardNumberValidator(value);
                   
                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }

                    if (result.card !== null) {
                        self.selectedCardType(result.card.type);
                        creditCardData.creditCard = result.card;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        var availableTypes = configFirstdata.availableCardTypes;
                        var typearray = $.map(availableTypes, function(value, index) {
                           return [index];
                       });
                       if($.inArray( result.card.type, typearray)!=-1)
                       {
                           self.creditCardType(result.card.type);
                       }
                       else
                       {
                           self.creditCardType("");
                       }
                       
                        
                       // self.creditCardType("");
                    }
                });

                //Set expiration year to credit card data object
                this.creditCardExpYear.subscribe(function (value) {
                    creditCardData.expirationYear = value;
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonth.subscribe(function (value) {
                    creditCardData.expirationMonth = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumber.subscribe(function (value) {
                    creditCardData.cvvCode = value;
                });
           },
            getCode: function () {
                return 'md_firstdata';
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transarmor_id': $('#'+this.getCode()+'_transarmor_id').val(),
                        'cc_type': $('#'+this.getCode()+'_cc_type').val(),
                        'cc_number': $('#'+this.getCode()+'_cc_number').val(),
                        'expiration': $('#'+this.getCode()+'_expiration').val(),
                        'expiration_yr': $('#'+this.getCode()+'_expiration_yr').val(),
                        'cc_cid': $('#'+this.getCode()+'_cc_cid').val(),
                        'save_card': $('#'+this.getCode()+'_save_card').val(),
                    }
                };
            },
             /**
             * Get list of available CC types
             *
             * @returns {Object}
             */
           
            getCcAvailableTypes: function() {

                 var availableTypes = configFirstdata.availableCardTypes;
               //  var typearray = $.makeArray(availableTypes);
                 var typearray = $.map(availableTypes, function(value, index) {
                    return [index];
                });
                return typearray;
            },
            getIcons: function (type) {
                return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type)
                    ? window.checkoutConfig.payment.ccform.icons[type]
                    : false
            },
            getCcMonths: function() {
                return window.checkoutConfig.payment.ccform.months[this.getCode()];
               // return configFirstdata.ccMonths;
            },
            getCcYears: function() {
                return window.checkoutConfig.payment.ccform.years[this.getCode()];
            },
            hasVerification: function() {
              //  return window.checkoutConfig.payment.ccform.hasVerification[this.getCode()];
                return configFirstdata.hasVerification;
            },
            hasSsCardType: function() {
                return window.checkoutConfig.payment.ccform.hasSsCardType[this.getCode()];
            },
            getCvvImageUrl: function() {
                return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()][0];
            },
            getCvvImageHtml: function() {
                return '<img src="' + this.getCvvImageUrl()
                    + '" alt="' + $t('Card Verification Number Visual Reference')
                    + '" title="' + $t('Card Verification Number Visual Reference')
                    + '" />';
            }, 
            getSsStartYears: function() {
                return window.checkoutConfig.payment.ccform.ssStartYears[this.getCode()];
            },
            getCcAvailableTypesValues: function() {
                return _.map(this.getCcAvailableTypes(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonthsValues: function() {
                return _.map(configFirstdata.ccMonths, function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYearsValues: function() {
                return _.map(configFirstdata.ccYears, function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            getSsStartYearsValues: function() {
                return _.map(this.getSsStartYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            isShowLegend: function() {
                return false;
            },
            getCcTypeTitleByCode: function(code) {
                var title = '';
                _.each(this.getCcAvailableTypesValues(), function (value) {
                    if (value['value'] == code) {
                        title = value['type'];
                    }
                });
                return title;
            },
            formatDisplayCcNumber: function(number) {
                return 'xxxx-' + number.substr(-4);
            },
            getInfo: function() {
                return [
                    {'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())},
                    {'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())}
                ];
            },
            getStoredCard: function(){
                
                return _.map(configFirstdata.storedCards, function(value, key) {
                    return {
                        'value': key,
                        'optText': value
                    }
                });
            },
            isNewEnabled: function(){
                var result = true;
                if(_.size(configFirstdata.storedCards) > 1){
                    result =  false;
                }
                return result;
            },
            isStoreCardDropdownEnabled: function()
            {
                var result = false;
                if(_.size(configFirstdata.storedCards) > 1){
                    result = true;
                }
                else if(_.size(configFirstdata.storedCards) == 1)
                {
                    this.newCardVisible(true);
                    this.noSavedCardAvail(true);
                }
                return result;
            },
            displayNewCard: function(){
                var elementValue = $('#'+this.getCode()+'_transarmor_id').val();
                if(elementValue == 'new'){
                    // $('#'+this.getCode()+'card-holder').css('display','block');
                     this.newCardVisible(true);
                     this.newCardVisibleSave(true);
                }else{
                    // $('#'+this.getCode()+'card-holder').css('display','none');
                     this.newCardVisible(false);
                     this.newCardVisibleSave(false);
                }
            },
            isSaveCardOptional: function(){
                return (configFirstdata.canSaveCard == 0) ? true: false;
            },
            prepareCsPayment: function(){
                if ($('#firstdata-transparent-form').valid()) {
                    this.placeOrder();
                  }
            },
           
        });
    }
);