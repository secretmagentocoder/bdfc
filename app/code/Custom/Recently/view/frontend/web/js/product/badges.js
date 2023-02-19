define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/product/list/column-status-validator'
], function (Column, columnStatusValidator) {
    'use strict';

    return Column.extend({

        /**
         * @param row
         * @returns {boolean}
         */
        hasValue: function (row) {
            return "badges" in row['extension_attributes'];
        },

        /**
         * @param row
         * @returns {*}
         */
        getValue: function (row) {
            return row['extension_attributes']['badges'];
        },

        /**
         * @param row
         * @returns {*|boolean}
         */
        isAllowed: function (row) {
            return (columnStatusValidator.isValid(this.source(), 'badges', 'show_attributes') && this.hasValue(row) );
        }

    });
});