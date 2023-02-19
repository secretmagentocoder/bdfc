var config = {
    map: {
        '*': {
            'Magento_Checkout/template/authentication.html':    'Sparsh_MobileNumberLogin/template/authentication.html',
            changeEmailMobilePassword: 'Sparsh_MobileNumberLogin/js/change-email-mobile-password'
        }
    },
    'config': {
        'mixins': {
            'Magento_Checkout/js/view/authentication': {
                'Sparsh_MobileNumberLogin/js/view/authentication': true
            }
        }
    }
};
