/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'mage/translate'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    quote,
    stepNavigator,
    paymentService,
    methodConverter,
    getPaymentInformation,
    checkoutDataResolver,
    $t
) {
    'use strict';

    var mixin = {

        defaults: {
            template: 'Bodak_CheckoutCustomForm/payment'
        },

        initialize: function () {
            var self = this;
            this._super();
        },

        // check step 2 store
        checkVisibleStepFlight: function(){
            return quote.isVirtual();
        },

        // only simple-> not visible delivery
        checkVisibleStepDelivery: function(){
            var flag = 'product';
            if (quote.getStoreCode() != 'home_delivery') {
                let quoteData = window.checkoutConfig.quoteItemData;
                _.each(quoteData,function(val,key){
                    if(val.is_virtual == 1){
                        flag = 'product_raffle';
                        return flag;
                    }
                });

            }
            return flag;
        },

        productSimpleVirtualExist: function (){
            var simple = '';
            var virtual = '';
            if (quote.getStoreCode() != 'home_delivery') {
                let quoteData = window.checkoutConfig.quoteItemData;
                _.each(quoteData,function(val,key){
                    if(val.is_virtual == 1){
                       virtual = 1;
                    }
                    if(val.is_virtual == 0){
                        simple = 1;
                    }
                });

                if(simple && virtual){
                    return true;
                }
                return false;

            }
        },

        /**
         * Show arrival form
         */
        showPrevStep: function () {
            stepNavigator.navigateTo('shipping');
            return false;
        },

        showPrevStepDelivery: function(item, event){
            // show step new
            $(event.target).parent().find('.co-step-title').addClass("active");
            $(event.target).parent().find('.co-step-content').addClass("active");
            // hide old  step

            
            return false;
        },

        isLoggin: function () {
          return customer.isLoggedIn();
        },

        navigateToNextStep: function () {
            stepNavigator.next();
        },


        arrival_departure_form_visible: function () {
            var current_store = quote.getStoreCode();
            var storeDepartureOrArrival = false;
            if(current_store == 'arrival' || current_store == 'departure'){
                storeDepartureOrArrival = true;
            }
            if (storeDepartureOrArrival && this.getIsVirtual() != 1) {
                return true;
            }

            return false;
        },

        /**
         * Show arrival form
         */
        arrival_form_visible: function () {
            var current_store = quote.getStoreCode();
            if (current_store == 'arrival' && this.getIsVirtual() != 1) {
                return true;
            }

            return false;
        },

        getIsVirtual: function () {
            var i = 0;
            if (quote.getStoreCode() != 'home_delivery') {
                let quoteData = window.checkoutConfig.quoteItemData;
                _.each(quoteData,function(val,key){
                    if(val.is_virtual == 1){
                        i = 1;
                        return i;
                    }
                })
            }
            return i;
        },

        /**
         * Show departure form
         */
        departure_form_visible: function () {
            // console.log(quote.getStoreCode());
            var current_store = quote.getStoreCode();
            if (current_store == 'departure'  && this.getIsVirtual() != 1) {
                return true;
            }

            return false;
        },

        /**
         * Show home_delivery form
         */
        home_delivery_form_visible: function () {
            var current_store = quote.getStoreCode();
            if (current_store == 'home_delivery') {
                return true;
            }

            return false;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };

});
