var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Amasty_Orderattr/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Amasty_Orderattr/js/action/place-order-mixin': true
            },
            'Amazon_Payment/js/action/place-order': {
                'Amasty_Orderattr/js/action/place-order-mixin': true
            },
            'Magento_Paypal/js/action/set-payment-method': {
                'Amasty_Orderattr/js/action/set-payment-method-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Amasty_Orderattr/js/action/set-payment-information-mixin': true
            }
        }
    }
};
