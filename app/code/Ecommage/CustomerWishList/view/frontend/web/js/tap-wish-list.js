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

    $.widget('mage.tap_wish_list', {
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
            this._click();
            var options         = this.options
            this._updatePrice();
        },

        _click: function(){
            var self = this;
            $('.qty-increase').on('click', function () {
                var id = ($(this).attr('id').replace('-dec',''));
                var currentQty = $("#cart-"+id+"-qty").val();
                if($(this).hasClass('qty-increase')){
                    var newQty = parseInt(currentQty)+parseInt(1);
                    self._ajaxQty(id, newQty);
                }
            })
            $('.qty-decrease').on('click', function () {
                var id = ($(this).attr('id').replace('-upt',''));
                var currentQty = $("#cart-"+id+"-qty").val();
                if($(this).hasClass('qty-decrease')){
                    var newQty = parseInt(currentQty) - parseInt(1);
                    if (newQty < 1 ){
                        newQty = 1;
                    }
                    self._ajaxQty(id, newQty);
                }
            })
        },

        _updatePrice: function ()
        {
            var self = this;
            $(document).on('change paste','.control.qty .input-text.qty', function(){
                var id = ($(this).attr('id').replace('cart-','')).replace('-qty','');
                var qty = $('#cart-'+ id +'-qty').val();
                if (qty < 1 ){
                    qty = 1;
                }
                $("#cart-"+id+"-qty").val(qty);
                self._ajaxQty(id, qty);
            });
        },

        _ajaxQty: function (id, qty)
        {
            var params = $('body').find('[data-item-id='+ id +']').data('post');
            params.data.qty = qty +".0000";
            $.ajax({
                url: this.options.url,
                data: {"id" : id,"qty": qty},
                showLoader: true,
                success: function (res) {
                    $("#cart-"+id+"-qty").val(qty);
                    $(".actions-primary ").find('[data-item-id= '+id+']').attr('data-post',JSON.stringify(params));
                    $("#cart-"+id+"-qty").attr('value',qty);
                }
            });
        }
    })

    return $.mage.tap_wish_list;
});
