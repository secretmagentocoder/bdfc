/**
 * bestseller-rate
 *
 * @copyright Copyright © 2021 Ecommage. All rights reserved.
 * @author    phuonghm@ecommage.com
 */

define([
    'underscore',
    'jquery',
    'jquery-ui-modules/widget',
    'mage/translate',
], function (_, $) {
    'use strict';

    $.widget('mage.search_order', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {},

        /**
         * Bind event handlers for adding contact phone.
         * @private
         */
        _create: function () {
            this._getAjax();
        },

        _getAjax : function () {
            $('body').on('change','#mySearch',function () {
                var textSearch = $(this).val();
                var ajaxUrl = window.BASE_URL + "ecommage_order_history/index/search";
                $.ajax({
                    url: ajaxUrl,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    data: {'text':textSearch},
                    success: function (data) {
                        console.log(data);
                        $('body').find('#order-history-content').html(data.html);
                       // $('body').find('#').html(data.html);
                    }
                })
            })
        }

    });

    return $.mage.search_order;
});
