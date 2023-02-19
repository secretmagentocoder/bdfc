define([
    'underscore',
    'jquery',
    'jquery-ui-modules/widget',
    'mage/translate',
], function (_, $) {
    'use strict';

    $.widget('mage.formAddNewAddress', {

        options: {},

        /**
         *
         * @private
         */
        _create: function () {
            var options = this.options
            this._changeCountry();
            this._defaultCountryCode();
        },

        /**
         *
         * @private
         */
        _changeCountry: function () {
            var self = this;
            $('body').on('click', 'ul#country-listbox li', function () {
                var countryCode = $(this).data('country-code');
                $('input[name="country_code"]').val(countryCode.toUpperCase());
            });
        },

        /**
         *
         * @private
         */
        _defaultCountryCode: function() {
            $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : '';
                $('input[name="country_code"]').val(countryCode);
            });
        }
    })

    return $.mage.formAddNewAddress;
});
