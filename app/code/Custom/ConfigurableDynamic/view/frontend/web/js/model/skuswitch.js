/**
 * Created by thomas on 2017-01-30.
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'mage/apply/main'
], function ($, wrapper, mage) {
    'use strict';
    return function(targetModule){
        var reloadPrice = targetModule.prototype._reloadPrice;
        targetModule.prototype.dynamic = {};
        targetModule.prototype.dynamicAttrs = [];

        $('[data-dynamic]').each(function(){
            var code = $(this).data('dynamic');
            var value = $(this).html();
            var attrs = [];

            // Get the initial attributes value to be able to reset them if a user selects an option and then selects none again
            $.each(this.attributes, function() {
                attrs[this.name] = this.value;
            });

            targetModule.prototype.dynamic[code] = value;
            targetModule.prototype.dynamicAttrs[code] = attrs;
        });

        var reloadPriceWrapper = wrapper.wrap(reloadPrice, function(original){
            //do extra stuff
            if (window.sku_value == '' || window.sku_value == undefined) {
                window.sku_value = $('div.product-info-main .product_sku .sku_value').html();
            }
            
            //call original method
            var result = original();

            //do extra stuff
            var simpleSku = this.options.spConfig.skus[this.simpleProduct];

            if(simpleSku != '' && simpleSku != undefined) {
                $('div.product-info-main .product_sku .sku_value').html(simpleSku);
            }else{
                $('div.product-info-main .product_sku .sku_value').html(window.sku_value);
            }


            //return original value
            return result;
        });

        targetModule.prototype._reloadPrice = reloadPriceWrapper;
        return targetModule;
    };
});
