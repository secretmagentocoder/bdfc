/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'escaper'
], function (Component, escaper) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details',
            allowedTags: ['b', 'strong', 'i', 'em', 'u']
        },

        isCheckRaffle: function (itemId) {
            var totalsData = window.checkoutConfig.totalsData.items;
            var flag = 1;
            _.each(totalsData,function (item) {
                if(item.item_id == itemId){
                    flag = item.is_check_raffle;
                }
            });
            return flag;
        },

        getItemBrand: function (itemId) {
            var totalsData = window.checkoutConfig.totalsData.items;
            var result = '';
            _.each(totalsData,function (item) {
                if(item.item_id == itemId){
                    result =  item.product_brand;
                }
            });
            return result;
        },

        getItemProductName: function (itemId) {
            var totalsData = window.checkoutConfig.totalsData.items;
            var result = '';
            _.each(totalsData,function (item) {
                if(item.item_id == itemId){
                    result = item.name;
                }
            });
            return result;
        },

        getItemProductPrice: function (itemId) {
            var totalsData = window.checkoutConfig.totalsData.items;
            var result = '';
            _.each(totalsData,function (item) {
                if(item.item_id == itemId){
                    result = item.product_price;
                }
            });
            return result;
        },

        getItemGiftWrap: function (itemId) {
            var totalsData = window.checkoutConfig.totalsData.items;
            var result = '';
            _.each(totalsData,function (item) {
                if(item.item_id == itemId){
                    result = item.is_gift_wrap;
                }
            });
            return result;
        },

        getItemProductSku: function (itemId) {
            var totalsData = window.checkoutConfig.totalsData.items;
            var result = '';
            _.each(totalsData,function (item) {
                if(item.item_id == itemId){
                    result = item.product_sku;
                }
            });
            return result;
        },

        getItemProductSize: function (itemId) {
            var totalsData = window.checkoutConfig.totalsData.items;
            var result = '';
            _.each(totalsData,function (item) {
                if(item.item_id == itemId){
                    result = item.product_size;
                }
            });
            return result;
        },

        getCountTicket: function (itemId) {
            var totalsData = window.checkoutConfig.totalsData.items;
            var result = '';
            _.each(totalsData,function (item) {
                if(item.item_id == itemId){
                    result = item.count;
                }
            });
            return result;
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getNameUnsanitizedHtml: function (quoteItem) {
            // console.log(quoteItem);
            var txt = document.createElement('textarea');

            txt.innerHTML = quoteItem.name;

            return escaper.escapeHtml(txt.value, this.allowedTags);
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getProductBrand: function (quoteItem) {
            var item_id = quoteItem.item_id;
            var product_brand = quoteItem.product_brand;
            var product_brand_key = 'item_id_'+item_id+'product_brand';
            if (product_brand != '' && product_brand != undefined) {
                window[product_brand_key] = product_brand;
            }
            product_brand = window[product_brand_key];

            return product_brand;
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getProductSku: function (quoteItem) {
            var item_id = quoteItem.item_id;
            var product_sku = quoteItem.product_sku;
            var product_sku_key = 'item_id_'+item_id+'product_sku';
            if (product_sku != '' && product_sku != undefined) {
                window[product_sku_key] = product_sku;
            }
            product_sku = window[product_sku_key];

            return product_sku;
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getProductSize: function (quoteItem) {
            var item_id = quoteItem.item_id;
            var product_size = quoteItem.product_size;
            var product_size_key = 'item_id_'+item_id+'product_size';
            if (product_size != '' && product_size != undefined) {
                window[product_size_key] = product_size;
            }
            product_size = window[product_size_key];

            return product_size;
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getProductOptions: function (quoteItem) {
            var item_id = quoteItem.item_id;
            var product_options = quoteItem.product_options;
            var product_options_key = 'item_id_'+item_id+'product_options';
            if (product_options != '' && product_options != undefined) {
                window[product_options_key] = product_options;
            }
            product_options = window[product_options_key];

            return product_options;
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getProductIsGiftWrap: function (quoteItem) {
            var item_id = quoteItem.item_id;
            var is_gift_wrap = quoteItem.is_gift_wrap;
            var is_gift_wrap_key = 'item_id_'+item_id+'is_gift_wrap';
            if (is_gift_wrap != '' && is_gift_wrap != undefined) {
                window[is_gift_wrap_key] = is_gift_wrap;
            }
            is_gift_wrap = window[is_gift_wrap_key];

            return is_gift_wrap;
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getProductPrice: function (quoteItem) {
            var item_id = quoteItem.item_id;
            var product_price = quoteItem.product_price;
            var product_price_key = 'item_id_'+item_id+'product_price';
            if (product_price != '' && product_price != undefined) {
                window[product_price_key] = product_price;
            }
            product_price = window[product_price_key];

            return product_price;
        }
    });
});
