define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/summary',
        'Magento_Checkout/js/model/step-navigator',
        'uiRegistry'
    ],
    function (
        $,
        ko,
        Component,
        stepNavigator,
        uiRegistry
    ) {
        'use strict';

        return Component.extend({

            isVisible: function () {
                return stepNavigator.isProcessed('shipping');
            },
            initialize: function () {
                var self = this;
                this._super();
                $(function () {
                    $('.payment-method-content').remove();
                    $(document).ready(function () {
                        $('body').on("click", '#place-order-trigger', function (e) {
                            $(".payment-method._active").find('.action.primary.checkout').trigger('click');
                            // set cookie if checkbox save_payment_detail checked
                            var paymentMethod = $(".payment-method._active").find('input[name="payment[method]"]').val();
                            var isSavePaymentDetail = $('input[name=save_payment_detail]').is(':checked');
                            if (isSavePaymentDetail && paymentMethod) {
                                self.setCookie('save_payment_detail', paymentMethod, 30);
                            }
                        });
                        // check Cookie get auto checked method

                    })
                });
            },

            setCookie: function (key, value, expiry) {
                var expires = new Date();
                expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
                document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
            },

            getCookie: function (key) {
                var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
                return keyValue ? keyValue[2] : null;
            },

            eraseCookie: function (key) {
                var keyValue = getCookie(key);
                setCookie(key, keyValue, '-1');
            },

        });
    }
);
