define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    var attributesTypes = [
            'amastyShippingAttributes',
            'amastyPaymentAttributes',
            'amastySummaryAttributes',
            'amastyShippingMethodAttributes',
            'before-place-order.amastyPaymentMethodAttributes'
        ],
        formCode = 'amasty_checkout';

    if (quote.isVirtual()) {
        attributesTypes = [
            'amastyPaymentAttributes',
            'before-place-order.amastyPaymentMethodAttributes',
            'amastySummaryAttributes'
        ];
        formCode = 'amasty_checkout_virtual';
    }

    return {
        'attributeTypes': attributesTypes,
        'formCode': formCode
    }
});
