/**
 * Created by Linh on 6/8/2016.
 * Updated on 20/09/2020
 */

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/url-builder',
        'mage/url',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, urlBuilder, url, quote){
        'use strict';
        // var paymentMethod = ko.observable(null);

        return Component.extend({
            redirectAfterPlaceOrder: false,

            defaults: {
                template: 'PL_Paygcc/payment/paygcc-apicheckout'
            },

            initialize: function() {
                this._super();
                self = this;
            },

            getCode: function() {
                return 'paygcc_benefit';
            },

            getData: function() {
                return {
                    'method': this.item.method
                };
            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('paygcc/benefit/redirect'));
            }

        });
    }
);