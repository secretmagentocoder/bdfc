var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Bodak_CheckoutCustomForm/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/payment': {
                'Bodak_CheckoutCustomForm/js/view/payment-mixin': true
            },
            'Magento_ReCaptchaFrontendUi/js/reCaptcha': {
                'Bodak_CheckoutCustomForm/js/view/recaptcha-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Bodak_CheckoutCustomForm/js/model/checkout-data-resolver': true
            }
        }
    }
};
