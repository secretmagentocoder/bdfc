var config = {
    map: {
        '*': {
            intlTelInput: 'Sparsh_MobileNumberLogin/js/intl-tel-input',
            intlTelInputUtils: 'Sparsh_MobileNumberLogin/js/utils',
            countryCode: 'Sparsh_MobileNumberLogin/js/country-code'
        }
    },
    shim: {
        intlTelInput: {
            deps: ['jquery']
        }
    }
};