define([
    'jquery',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-payment-method'
], function(
    $,
    paymentService,
    checkoutData,
    selectPaymentMethodAction
) {
    'use strict';

    return function(checkoutDataResolver) {
        checkoutDataResolver.resolvePaymentMethod = function() {
            var availablePaymentMethods = paymentService.getAvailablePaymentMethods(),
                selectedPaymentMethod = $.cookie('save_payment_detail') ? $.cookie('save_payment_detail') : checkoutData.getSelectedPaymentMethod();
            if (selectedPaymentMethod) {
                availablePaymentMethods.some(function (payment) {
                    if (payment.method == selectedPaymentMethod) {
                        selectPaymentMethodAction(payment);
                    }
                });
            }
        };

        return checkoutDataResolver;
    };
});
