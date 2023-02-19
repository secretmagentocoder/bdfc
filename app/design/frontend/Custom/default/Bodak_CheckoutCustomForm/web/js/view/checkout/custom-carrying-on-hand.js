/*global define*/
define([
    'knockout',
    'jquery',
    'uiRegistry',
    'mage/url',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/cart/cache',
    'Bodak_CheckoutCustomForm/js/model/checkout/custom-carrying-on-hand'
], function (ko, $, uiRegistry, urlFormatter, Component, customer, quote, urlBuilder, errorProcessor, cartCache, formData) {
    'use strict';

    return Component.extend({
        customFields: ko.observable(null),
        formData: formData.customFieldsData,

        /**
         * Initialize component
         *
         * @returns {exports}
         */
        initialize: function () {
            var self = this;
            this._super();
            formData = this.source.get('customCheckoutForm');

            var formDataCached = cartCache.get('custom-form');
            if (formDataCached) {
                formData = this.source.set('customCheckoutForm', formDataCached);
            }

            // arrivalCarryingOnHand
            this.arrivalCarryingOnHand(formData);

            this.customFields.subscribe(function (change) {
                self.formData(change);
            });
              self.clickOption();

            // call api airline info
            $(document).on('change', 'input[name=arrival_flight_date]', function () {
        
                if ($('input[name="arrival_flight_number"]').val() && $(this).val()) {
                    self.checkArrivalApi($(this).val(), $('input[name="arrival_flight_number"]').val());
                }
            });
            $(document).on('change', 'input[name=arrival_flight_number]', function () {
               
                if ($('input[name="arrival_flight_date"]').val() && $(this).val()) {
                    self.checkArrivalApi($('input[name="arrival_flight_date"]').val(), $(this).val());
                }
            });

            // onValidate when click continue
            $(document).on('click', '.actions-toolbar button[data-role=opc-continue-delivery]', function (e) {
     
                if (quote.getStoreCode() == 'arrival') {
                    var formFlight = {
                        arrival_pick_up_by_another_person: $('body').find('select[name=arrival_pick_up_by_another_person]').val(),
                        arrival_another_person_name: $('body').find('input[name=arrival_another_person_name]').val(),
                        arrival_another_person_phone: $('body').find('input[name=arrival_another_person_phone]').val(),
                        arrival_do_you_have_quantity_on_hand: null,
                        arrival_flight_date: $('body').find('input[name=arrival_flight_date]').val(),
                        arrival_flight_number: $('body').find('input[name=arrival_flight_number]').val(),
                        arrival_allowance_limit: $('body').find('input[name=arrival_allowance_limit]').is(':checked'),
                    }

                    if (!self.onValidateArrivalFlightInfo(formFlight)) {
                        e.preventDefault();
                        if (!$('#opc-custom-checkout-form .button-flight-next-to-delivery div').hasClass('field-error')) {
                            $('#opc-custom-checkout-form .button-flight-next-to-delivery').append('<div class="field-error"><span>Please fill all required fields</span></div>');
                        }
                    } else {
                        if ($('div[name="customCheckoutForm.arrival_flight_number"] div:last-child').hasClass('field-error')) {
                            e.preventDefault();
                            $('#opc-custom-checkout-form div:last-child').find('.field-error').remove();
                            // $('#opc-custom-checkout-form div:last-child').append('<div class="field-error flight-infomation"><span>Invalid flight information</span></div>');
                            
                        } else {
                            $('#button-flight-next-to-delivery').find('.field-error').remove();

                            // next to step + active class
                            if(self.getIsVirtual()){
                                $('.checkout-shipping-method').addClass("active");
                                $('.checkout-shipping-method').removeClass("visible");

                                $('li#opc-custom-checkout-form').find('.co-step-title').removeClass("active");
                                $('li#opc-custom-checkout-form').find('.co-step-content').removeClass("active");
                                $('li#opc-departure-checkout-form').find('.co-step-title').removeClass("active");
                                $('li#opc-departure-checkout-form').find('.co-step-content').removeClass("active");
                                
                                // $('.custom-checkout-step').find('.co-step-title').removeClass("prev_step");
                                $('li#opc-custom-checkout-form').find('.co-step-title').addClass("prev_step");
                                
                                $('li#opc-delivery-checkout-form').find('.co-step-title').addClass("active");
                                $('li#opc-delivery-checkout-form').find('.co-step-content').addClass("active");
                            }else{
                                 // next to payment 
                                 $("button[data-trigger=continue-payment]").trigger("click");
                            }
                        }
                    }
                }

            });

            return this;
        },

        /*
         * validate required form flight infomation
         * */
        onValidateArrivalFlightInfo: function (formData) {
            var flag = true;
                if (formData.arrival_pick_up_by_another_person == 'Pick Up By Another Person') {
                    if (!formData.arrival_another_person_name || !formData.arrival_another_person_phone || !formData.arrival_flight_number || !formData.arrival_flight_date) {
                        flag = false;
                    } else {
                        flag = true;
                    }
                } else {
                    if (!formData.arrival_flight_number || !formData.arrival_flight_date
                    ) {
                        flag = false;
                    } else {
                        flag = true;
                    }
                }
            return flag;
        },

        /**
         *  CHECK EXIST VIRTUAL PRODUCT
         * @returns {string}
         */
         getIsVirtual: function () {
            if (quote.getStoreCode() != 'home_delivery') {
                var i = 0;
                let quoteData = window.checkoutConfig.quoteItemData;
                _.each(quoteData,function(val,key){
                    if(val.is_virtual == 1){
                        i = 1;
                        return i;
                    }
                });
            }
            return i;
        },



            clickOption : function () {
                        if (quote.getStoreCode() == 'arrival')
                        {
                            $('body').on('click','#form-show-category input[type=radio]',function () {
                                if ($(this).val() == '1')
                                {
                                    $('body').find('input[name="yes"]').prop('checked',true);
                                    $('body').find('input[name="no"]').prop('checked',false);
                                    $('#arrival_carrying_on_hands').show();
                                }else {
                                    $('body').find('input[name="yes"]').prop('checked',false);
                                    $('body').find('input[name="no"]').prop('checked',true);
                                    $('#arrival_carrying_on_hands').hide();
                                }

                            })
                        }
                    },


        /*
         * call api + render html airline infomation
         *  */
        checkArrivalApi: function (arrivalDate, flightNo) {
            var ajaxUrl = 'ecommage_checkoutdata/ajax/departure';
            var date = arrivalDate.replace('/', '-').replace('/', '-');

            $.ajax({
                url: urlFormatter.build(ajaxUrl),
                dataType: 'json',
                data: {
                    'arrivalDate': date,
                    'flightNo': flightNo
                },
                showLoader: true,
                success: function (res) {
                    if (res) {
                        var ajaxUrl = 'bdfc_general/ajax/departure';
                        $.ajax({
                            url: urlFormatter.build(ajaxUrl),
                            dataType: 'json',
                            data: {
                                'arrival_flight_date': date,
                                'arrival_flight_time': res.arrival_time
                            },
                            complete: function (response) {
                                if (response.responseJSON.success == true) {
                                    return true;
                                } else {
                                    $('#arrival-flight-details').hide();
                                    $('div[name="customCheckoutForm.arrival_flight_number"]').append('<div class="field-error"><span>'+response.responseJSON.message+'</span></div>');
                                    $('body').find('.button-flight-next-to-delivery button').attr('disabled',true);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.log('Error happens. Try again.');
                            }
                        });
                        $('#arrival_airline_logo').attr('src', res.airline_logo);
                        $('#arrival_flight_name').html(res.airline_name);
                        $('#arrival_flight_origin').html(res.origin);
                        $('#arrival_flight_time').html(res.arrival_time);
                    
                        $('#arrival-flight-details').show();
                        $('div[name="customCheckoutForm.arrival_flight_number"]').find('.field-error').remove();
                    
                        $('#checkout-step-shipping_method').find('.field-error .flight-infomation').remove();
                        
                        if (res.airline_name) {
                            $('body').find('.button-flight-next-to-delivery button').attr('disabled',false);
                            $('#flight-error').html('');
                        } else {
                            $('#flight-error').html('Wrong flight information please try again');
                            $('#flight-error').css('color', 'red');
                            $('body').find('.button-flight-next-to-delivery button').attr('disabled',true);
                        }
                    } else {
                        $('#arrival-flight-details').hide();
                    
                        if (!$('div[name="customCheckoutForm.arrival_flight_number"] div:last-child').hasClass('field-error')) {
                            $('div[name="customCheckoutForm.arrival_flight_number"]').append('<div class="field-error"><span>Invalid flight information</span></div>');

                            $('body').find('.button-flight-next-to-delivery button').attr('disabled',true);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error)
                }
            })
        },

        getHtmlCustomCategory: function () {
            return window.checkoutConfig.custom_data_checkout;
        },

        /**
         * @returns void
         */
        arrivalCarryingOnHand: function (formData) {
            const customFormData = this.source.get('customCheckoutForm');
            // console.log(customFormData);
            if (customFormData != '' && customFormData != undefined) {
                const arrival_quantity_on_hand = customFormData.arrival_quantity_on_hand;
                var on_hand_qty_spirit_wine = 0;
                var on_hand_qty_beer = 0;
                var on_hand_qty_tobacco = 0;
                var on_hand_qty_flv_tobacco = 0;
                const arrival_quantity_on_hand_obj = jQuery.parseJSON(arrival_quantity_on_hand);
                if (arrival_quantity_on_hand_obj != null) {
                    if (arrival_quantity_on_hand_obj[0] != undefined) {
                        on_hand_qty_spirit_wine = arrival_quantity_on_hand_obj[0].on_hand_qty_spirit_wine;
                    }
                    if (arrival_quantity_on_hand_obj[0] != undefined) {
                        on_hand_qty_beer = arrival_quantity_on_hand_obj[0].on_hand_qty_beer;
                    }
                    if (arrival_quantity_on_hand_obj[0] != undefined) {
                        on_hand_qty_tobacco = arrival_quantity_on_hand_obj[0].on_hand_qty_tobacco;
                    }
                    if (arrival_quantity_on_hand_obj[0] != undefined) {
                        on_hand_qty_flv_tobacco = arrival_quantity_on_hand_obj[0].on_hand_qty_flv_tobacco;
                    }
                }

                //
                $('#custom-checkout-form input[name="on_hand_qty_spirit_wine"]').val(on_hand_qty_spirit_wine);
                $('#custom-checkout-form input[name="on_hand_qty_beer"]').val(on_hand_qty_beer);
                $('#custom-checkout-form input[name="on_hand_qty_tobacco"]').val(on_hand_qty_tobacco);
                $('#custom-checkout-form input[name="on_hand_qty_flv_tobacco"]').val(on_hand_qty_flv_tobacco);
            }
        },

        /**
         * @returns void
         */
        onHandQtySpiritWine: function () {
            const customFormData = this.source.get('customCheckoutForm');
            // console.log(customFormData);
            var on_hand_qty_spirit_wine = 0;
            if (customFormData != '' && customFormData != undefined) {
                const arrival_quantity_on_hand = customFormData.arrival_quantity_on_hand;
                const arrival_quantity_on_hand_obj = jQuery.parseJSON(arrival_quantity_on_hand);
                if (arrival_quantity_on_hand_obj != null) {
                    if (arrival_quantity_on_hand_obj[0] !== undefined) {
                        on_hand_qty_spirit_wine = arrival_quantity_on_hand_obj[0].on_hand_qty_spirit_wine;
                    }
                }
            }

            return on_hand_qty_spirit_wine;
        },

        /**
         * @returns void
         */
        onHandQtyBeer: function () {
            const customFormData = this.source.get('customCheckoutForm');
            // console.log(customFormData);
            var on_hand_qty_beer = 0;
            if (customFormData != '' && customFormData != undefined) {
                const arrival_quantity_on_hand = customFormData.arrival_quantity_on_hand;
                const arrival_quantity_on_hand_obj = jQuery.parseJSON(arrival_quantity_on_hand);
                if (arrival_quantity_on_hand_obj != null) {
                    if (arrival_quantity_on_hand_obj[0] !== undefined) {
                        on_hand_qty_beer = arrival_quantity_on_hand_obj[0].on_hand_qty_beer;
                    }
                }
            }

            return on_hand_qty_beer;
        },

        /**
         * @returns void
         */
        onHandQtyTobacco: function () {
            const customFormData = this.source.get('customCheckoutForm');
            // console.log(customFormData);
            var on_hand_qty_tobacco = 0;
            if (customFormData != '' && customFormData != undefined) {
                const arrival_quantity_on_hand = customFormData.arrival_quantity_on_hand;
                const arrival_quantity_on_hand_obj = jQuery.parseJSON(arrival_quantity_on_hand);
                if (arrival_quantity_on_hand_obj != null) {
                    if (arrival_quantity_on_hand_obj[0] !== undefined) {
                        on_hand_qty_tobacco = arrival_quantity_on_hand_obj[0].on_hand_qty_tobacco;
                    }
                }
            }

            return on_hand_qty_tobacco;
        },

        /**
         * @returns void
         */
        onHandQtyFlvTobacco: function () {
            const customFormData = this.source.get('customCheckoutForm');
            // console.log(customFormData);
            var on_hand_qty_flv_tobacco = 0;
            if (customFormData != '' && customFormData != undefined) {
                const arrival_quantity_on_hand = customFormData.arrival_quantity_on_hand;
                const arrival_quantity_on_hand_obj = jQuery.parseJSON(arrival_quantity_on_hand);
                if (arrival_quantity_on_hand_obj != null) {
                    if (arrival_quantity_on_hand_obj[0] !== undefined) {
                        on_hand_qty_flv_tobacco = arrival_quantity_on_hand_obj[0].on_hand_qty_flv_tobacco;
                    }
                }
            }

            return on_hand_qty_flv_tobacco;
        }
    });

});
