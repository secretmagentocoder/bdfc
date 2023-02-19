define([
    'underscore',
    'mageUtils',
    'Amasty_Orderattr/js/form/element/select',
    'Amasty_Orderattr/js/form/relationAbstract'
], function (_, utils, Select, relationAbstract) {
    'use strict';

    return Select.extend(relationAbstract).extend({
        isFieldInvalid: function () {
            return this.error() && this.error().length ? this : null;
        },

        /** Fix default first option selection **/
        normalizeData: function (value) {
            if (utils.isEmpty(value)) {
                return '';
            }
            var option = this.getOption(value);

            return option && option.value;
        },

        /**
         * Clears 'value' property.
         *
         * @returns {Abstract} Chainable.
         */
        clear: function () {
            this.value('');

            return this;
        }
    });
});
