define([
    'Amasty_Orderattr/js/action/amasty-validate-form',
    'Amasty_Orderattr/js/model/attribute-sets/payment-attributes'
], function (validateForm, formData) {
    'use strict';

    return {
        /**
         * Validate checkout agreements
         *
         * @returns {boolean}
         */
        validate: function() {
            window.orderAttributesPreSend = validateForm(formData.attributeTypes);
            return window.orderAttributesPreSend;
        }
    }
});
