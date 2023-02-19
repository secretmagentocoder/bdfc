/**
 * bestseller-rate
 *
 * @copyright Copyright © 2021 Ecommage. All rights reserved.
 * @author    phuonghm@ecommage.com
 */

define([
    'underscore',
    'jquery',
    'priceUtils',
    'rjsResolver',
    'jquery-ui-modules/widget',
    'mage/translate',
], function (_, $,priceUtils,resolver) {
    'use strict';

    $.widget('mage.raffle_ticket', {
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
            resolver(function () {
                $('body').find('.breadcrumbs ul.items li.home a').attr('href',this.options.breadcrumbsUrl);
            }.bind(this));
            $('.btn-submit-number').eq(0).addClass('active');
            this.setOption();
            this._changOption();
            this._searchTicket();
            this._submitAddToCart();
            this._getParams();

        },


        _getParams : function () {
            let searchParams = new URLSearchParams(window.location.search);
            let options = searchParams.get('op');
            $('#product_addsumbit_form').attr('option',options);
            if (options){
                this._checkBox();
                this.setOption();
                $('body').find('.admin__field-option').trigger('click');
            }
        },

     setOption : function () {
            var $option = this.options.optionConfig,
                value = [],
                self = this,
                options = $('input.product-customs-options');
            $('.options-list.nested').on('click','.admin__field-option',function () {
                value = $option[$(this).data('id')][$(this).data('option-id')];
                $(this).toggleClass(".main_class");
                self._setValue(value);
                var check = $('input[value='+$(this).data('option-id')+']:checked');
                if (check.length > 0){
                    $(this).addClass('active');
                }else {
                    $(this).removeClass('active');
                }
                options.attr('value',$(this).data('option-id'));
                $('body').find('#tab-right').css('display','block');
            
            })
        },
         _checkBox : function () {
            var option = $('#product_addsumbit_form').attr('option');
            if (option) {
            $.each(option.split(','),function (key,value) {
                let element = $('#product_addsumbit_form').find('input[data-label='+ value+']');
                element.prop('checked',true);
                $('.options-list').find('div[data-label='+ value+']').addClass('active');
            });
            $('#product_addsumbit_form').trigger('contentUpdated');
            }
        },


        _setValue: function () {
                var $option = this.options.optionConfig;
                var selector = $('div.options-list div.admin__field-option');
                var text = '',
                    price = 0,
                    tax = 0,
                    exclTax = 0;
                var self = this;
            selector.find("input:checkbox:checked").each(function () {
                    text += $(this).data('label') + ', ';
                    var present = Object.keys($option[$(this).data('id')])[0];
                    if ($option[$(this).data('id')][present]){
                        price += Number($option[$(this).data('id')][present].prices.finalPrice.amount);
                        exclTax += Number($option[$(this).data('id')][present].prices.basePrice.amount);
                        tax += $option[$(this).data('id')][present].prices.basePrice.amount - $option[$(this).data('id')][present].prices.finalPrice.amount;
                    }
                });

                text = text.substring(0, text.length - 2);
                $('body').find('.title-booked').text(text);
                $('body').find('#product_addsumbit_form').attr('option',text)
                $('body').find('.no-of-title').text(selector.find("input:checkbox:checked").length);
                $('body').find('.ticket-excl-vat').text(self._formatPrice(exclTax));
                $('body').find('.total-cart-title').text(self._formatPrice(price));
                $('body').find('.total-vat').text(self._formatPrice(tax));
            },

        _formatPrice : function (value) {
            var price;
            let priceFormat = {
                groupLength: 3,
                groupSymbol: ".",
                integerRequired: false,
                pattern: "BHD %s",
                requiredPrecision: 3
            };
            price =  priceUtils.formatPrice(
                value,
                priceFormat,
                false
            );

            return price.replace(',','.');
        },


      _changOption : function () {
            var self = this;
            var btn = this.options.type;
            $(btn).on('click',function () {
                $(this).prevAll().removeClass('active').eq($(this).index());
                $(this).nextAll().removeClass('active').eq($(this).index());
                $(this).addClass('active');
                var param = $(this).data('type');
                var productId = $('input[name=product]').val();
                var ajaxUrl = window.BASE_URL + "ecommage_raffle_tickets/action/change";
                $.ajax({
                    url: ajaxUrl,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    data: { 'id': productId,'type':param },
                    success: function (data) {
                        $('body').find('.options-list.nested').html(data.html);
                        self._checkBox();
                        self.setOption();
                        $('input[name=search]').trigger('keyup');
                    }
                })
            })
        },

        _searchTicket : function () {
            $('input[name=search]').on('keyup',function () {
                var input, filter, ul, li, a, i, txtValue;
                input = $(this).val();
                filter = input.toUpperCase();
                li = $('.options-list.nested div');
                for (i = 0; i < li.length; i++) {
                    a = li[i].getElementsByTagName("label")[0];
                    if (a) {
                    txtValue = a.textContent || a.innerText;

                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        li[i].style.display = "";
                    } else {
                        li[i].style.display = "none";
                    }
                }
                }
            })
        },

        _submitAddToCart : function () {
            var self = this;
            let searchParams = new URLSearchParams(window.location.search);
            let id = searchParams.get('id');
            $('body').on('click','#product-addtocart-button' ,function () {
                var form = $('#product_addtocart_form'),
                    baseUrl = form.attr('action');
                if (id)
                {
                    baseUrl = baseUrl + '?id=' + id ;
                }
                $('#product_addsumbit_form').attr('action', baseUrl);
                $('#product_addsumbit_form').trigger('submit');
                return false;
            })
        }

    })

    return $.mage.raffle_ticket;
});

// $(document).ready(function(){
//     $(".admin__field-label").click(function(){
//       $(this).toggleClass("main_class");
//     });
//   });
