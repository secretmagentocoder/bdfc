define([
    'underscore',
    'jquery',
    'jquery-ui-modules/widget',
    'mage/translate',
], function (_, $) {
    'use strict';

    $.widget('mage.accountEdit', {

        options: {},

        /**
         *
         * @private
         */
        _create: function () {
            var options = this.options
            this._changeCountry();
            this._defaultCountryCode();
            // if (options.optionNational.optionData && this._isRegionOption()) {
            //     this._renderOption(options.optionNational.optionData.value);
            // }
            // if(!this._isRegionOption()){
            //     var customOptions = this.options.nationalOptionCustom.options;
            //     this._renderCustomOptions(customOptions);
            // }
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
                if (countryCode) {
                    self._renderOption(countryCode.toUpperCase());
                }
            });
        },

        /**
         *
         * @param nationData
         * @private
         */
        _renderOption: function (nationData) {
            var self = this;
            var options = ``;
            var customOptions = this.options.nationalOptionCustom.options;
            var data = self.options.regionData[0][nationData];
            if(data == undefined){
                self._renderCustomOptions(customOptions);
                return;
            }
            _.each(data, function (val, key) {
                var text = '';
                var option = self.options.optionNational;
                if (option.optionData != undefined && option.optionData.id == key) {
                    text = 'selected';
                }
                options += "<option " + text + " value=" + key + ">" + val + "</option>";
            })
            $('#nationality').html(options);
        },

        /**
         *
         * @param data
         * @private
         */
        _renderCustomOptions: function (data) {
            var self = this;
            var options = ``;
            var keyCustomOption = 'cus_';
            _.each(data, function (val) {
                var text = '';
                var option = self.options.isRegionOption;

                if (option.id != undefined && option.id == 'cus_'+val.value) {
                    text = 'selected';
                }
                options += "<option " + text + " value="+keyCustomOption+ val.value + ">" + val.label + "</option>";
            })
            $('#nationality').html(options);
        },

        /**
         *
         * @returns {*}
         * @private
         */
        _isRegionOption: function (){
            return this.options.isRegionOption.is;
        },

        /**
         *
         * @private
         */
        _defaultCountryCode: function() {
            $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                if(!$('input[name="country_code"]').val()){
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    $('input[name="country_code"]').val(countryCode);
                }
            });
        }
    })

    return $.mage.accountEdit;
});
