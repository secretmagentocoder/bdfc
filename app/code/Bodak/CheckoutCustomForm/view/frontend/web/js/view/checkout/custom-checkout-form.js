/*global define*/
define([
    'knockout',
    'jquery',
    'uiRegistry',
    'mage/url',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/cart/cache',
    'Bodak_CheckoutCustomForm/js/model/checkout/custom-checkout-form',
    'mage/calendar',
    'Magento_Checkout/js/action/get-totals'
], function (ko, $, uiRegistry, urlFormatter, Component, customer, customerData, quote,createBillingAddress, urlBuilder, errorProcessor, defaultTotal, cartCache, formData,calendar,getTotalsAction) {
    'use strict';

    ko.bindingHandlers.datepicker = {
        init: function (element, valueAccessor, allBindingsAccessor) {
            var options = allBindingsAccessor().datepickerOptions || {},
                $el = $(element);

            //initialize datepicker with some optional options
            $el.datepicker(options);

            //handle the field changing
            ko.utils.registerEventHandler(element, "change", function () {
                var observable = valueAccessor();
                // observable($el.datepicker("getDate"));
                var date = Date.parse($(element).val());
            });

            //handle disposal (if KO removes by the template binding)
            ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                $el.datepicker("destroy");
            });
        }
    };

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

            this.customFields.subscribe(function (change) {
                self.formData(change);
            });

            // terms_and_conditions_popup
            $(document).ready(function () {
                $(document).on('click', '.terms_and_conditions', function () {
                    $("#terms_and_conditions_popup").addClass("open");
                });
                $(document).on('click', '.custom_popup_close', function () {
                    $("#terms_and_conditions_popup").removeClass("open");
                });
            });

            // trigger email custom to email username
                $('body').find('form[data-role="email-with-possible-login"]').hide();

                $('body').on('blur','#shipping-new-address-form input[name="email"]',function () {
                    let emailCusVal = $(this).val();
                    $('body').find('form[data-role="email-with-possible-login"] input[name=username]').val(emailCusVal);
                    $('body').find('form[data-role="email-with-possible-login"] input[name=username]').trigger('change');
                });

                // set default value of form custom to form billing default
                var addressDefault = self.getAddressDefaultCustom();
                $('body').on('change','input[name="payment[method]"]', function () {

                    var firstname = $('form#co-shipping-form input[name="firstname"]').val();
                    var lastname = $('form#co-shipping-form input[name="lastname"]').val();
                    var mobile = $('form#co-shipping-form input[name="mobile_number"]').val();

                    $(this).parent().parent().find('.payment-method-billing-address input[name="custom_attributes[dateofbirth]"]').val(addressDefault.dateofbirth).trigger('change');
                    $(this).parent().parent().find('.payment-method-billing-address input[name="firstname"]').val(firstname).trigger('change');
                    $(this).parent().parent().find('.payment-method-billing-address input[name="lastname"]').val(lastname).trigger('change');
                    $(this).parent().parent().find('.payment-method-billing-address input[name="telephone"]').val(mobile).trigger('change');
                    // $(this).parent().parent().find('.payment-method-billing-address input[name="street[1]"]').val('Pickup from Arrival Store 1').trigger('change');

                    $(this).parent().parent().find('.payment-method-billing-address button.action-update').trigger('click');
                });

                // custom deliverys tep in departure && arrival store
                $(document).ready(function () {
                    // trigger next from delivery to payment
                    $('body').on('click','.step4-next-payment',function(){
                        $('#shipping-method-buttons-container').find('button[data-trigger=continue-payment]').trigger('click');
                    });

                    // validate step delivery arrival
                    $('body').on('click','#shipping-method-buttons-container button[data-trigger=continue-payment]',function (e) {
                        if(quote.getStoreCode() == 'arrival' && self.getIsVirtual()){
                            var street = $('#opc-delivery-checkout-form input[name="street[]"]').map(function(){return $(this).val();}).get();

                            var validate  = self.validateStreet(street);
                            console.log($('li#opc-delivery-checkout-form .co-step-content div:last-child').hasClass('field-error'));
   
                            if(validate == 1 && !$('li#opc-delivery-checkout-form .co-step-content div:last-child').hasClass('field-error')){
                                $('li#opc-delivery-checkout-form .co-step-content').append('<div class="field-error delivery-infomation"><span>Please fill all required fields</span></div>');
                                return false;
                            }
                            if(validate != 1){
                                $('li#opc-delivery-checkout-form .co-step-content').find('.field-error').remove();
                                return true;
                            }
                        }
                    });

                    // validate step delivery in store departure
                    $('body').on('click','#shipping-method-buttons-container button[data-trigger=continue-payment]',function (e) {
                        if(quote.getStoreCode() == 'departure' && self.getIsVirtual()){
                            var street = $('#opc-delivery-checkout-form input[name="street[]"]').map(function(){return $(this).val();}).get();

                            var validate  = self.validateStreet(street);
    
                            if(validate == 1 && !$('li#opc-delivery-checkout-form .co-step-content div:last-child').hasClass('field-error')){
                                $('li#opc-delivery-checkout-form .co-step-content').append('<div class="field-error delivery-infomation"><span>Please fill all required fields</span></div>');
                                e.preventDefault();
                                return false;
                            }else{
                                // stepNavigator.next();
                            }
                            if(validate != 1){
                                $('li#opc-delivery-checkout-form .co-step-content').find('.field-error').remove();
                                return true;
                            }
                        }
                    });

                    // validate homedelivery form
                    $('body').on('click','#shipping-method-buttons-container button[data-trigger=continue-payment]',function (e) {
                        if(quote.getStoreCode() == 'home_delivery'){
                            var street = $('.shipping-address-delivery input[name="street[]"]').map(function(){return $(this).val();}).get();

                            var validate  = self.validateStreet(street);
    
                            if(validate == 1 && !$('.shipping-address-delivery .delivery-form div:last-child').hasClass('field-error')){
                                $('.shipping-address-delivery .delivery-form').append('<div class="field-error delivery-infomation"><span>Please fill all required fields</span></div>');
                                e.preventDefault();
                                return false;
                            }
                            if(validate != 1){
                                $('.shipping-address-delivery .delivery-form div').find('.field-error').remove();
                                return true;
                            }
                        }
                    });

                    $('body').on('click', '#opc-custom-checkout-form button[data-role=opc-continue-delivery]', function(){
                        var formFlight = {
                            arrival_another_person_phone: $("#custom-checkout-form .iti__selected-flag .iti__selected-dial-code").text()+'-'+$('body').find('input[name=arrival_another_person_phone]').val(),
                            arrival_collection_point: 'Duty free shop'
                        }
                        self.setCheckoutSession(formFlight);
                    });
                });
                

            return this;
        },

        /**
         *  validate street
         * @param {*} $arr 
         * @returns 
         */
        validateStreet : function (arr) {
            var i = '';
            $.each(arr , function (key,value) {
                if (!value){
                   i = 1;
                }
            })
            return i;
        },

        /*
         * set checkout session
         * */
        setCheckoutSession: function (formFlight) {
            $.ajax({
                url: urlFormatter.build('bdfc_general/checkout/index'),
                method: 'POST',
                data: {
                    formFlight: formFlight
                },
                showLoader: true,
                success: function (resp) {
                    console.log('done');
                }
            });
        },

        onlyRegularProduct: function(){
            var i = 'none';
            if (quote.getStoreCode() != 'home_delivery') {
                i = 'regular';
                let quoteData = window.checkoutConfig.quoteItemData;
                _.each(quoteData,function(val,key){
                    if(val.is_virtual == 1){
                        i = 'virtual';
                        return i;
                    }
                })
            }
            return i;
        },

        onlyVirtual: function(){
            return quote.isVirtual();
        },

        getIsVirtual: function () {
            var i = 0;
            if (quote.getStoreCode() != 'home_delivery') {
                let quoteData = window.checkoutConfig.quoteItemData;
                _.each(quoteData,function(val,key){
                    if(val.is_virtual == 1){
                        i = 1;
                        return i;
                    }
                })
            }
            return i;
        },

        /**
         * Trigger save method if form is change
         */
        onFormChange: function (value) {
            // console.log("sds");
            this.fieldDepend(value);

            var response = false;
            var response = this.saveCustomFields();

            if (response == true) {
                this.updateAfterChange();
            }
        },

        getAddressDefaultCustom: function () {
            let address = window.checkoutConfig.addressDefaultCustom;
            return address;
        },

        getDobFormat: function () {
            return '';
        },

        // getPickUpByAnotherPerson: function () {
        //     let options = window.checkoutConfig.departure_select_your_lounge;
        //     return options;
        // },

        /**
         * Update field dependency
         *
         * @param {String} value
         */
        fieldDepend: function (value) {
            // console.log("sds");
            setTimeout(function () {
                // Flight Details
                let arrival_flight_date = $('#custom-checkout-form input[name="arrival_flight_date"]').val();
                let arrival_flight_number = $('#custom-checkout-form input[name="arrival_flight_number"]').val();

                // End Flight details

                var arrival_pick_up_by_another_person_field = $('#custom-checkout-form select[name="arrival_pick_up_by_another_person"]');
                var arrival_another_person_name_field = $('#custom-checkout-form input[name="arrival_another_person_name"]');
                var arrival_another_person_phone_field = $('#custom-checkout-form input[name="arrival_another_person_phone"]');
                var arrival_number_of_co_traveller_field = $('#custom-checkout-form select[name="arrival_number_of_co_traveller"]');
                var arrival_co_traveller_full_name_field = $('#custom-checkout-form input[name="arrival_co_traveller_full_name"]');
                var arrival_co_traveller_dob_field = $('#custom-checkout-form input[name="arrival_co_traveller_dob"]');
                var arrival_do_you_have_quantity_on_hand_field = uiRegistry.get('index = arrival_do_you_have_quantity_on_hand');

                var arrival_pick_up_by_another_person = arrival_pick_up_by_another_person_field.val();
                if (arrival_pick_up_by_another_person == "Pick Up By Another Person") {
                    arrival_another_person_name_field.parents('.field').show();
                    arrival_another_person_phone_field.parents('.field').show();
                } else {
                    arrival_another_person_name_field.parents('.field').hide();
                    arrival_another_person_phone_field.parents('.field').hide();
                }

                var arrival_number_of_co_traveller = arrival_number_of_co_traveller_field.val();
                if (arrival_number_of_co_traveller == "00") {
                    arrival_co_traveller_full_name_field.parents('.field').hide();
                    arrival_co_traveller_dob_field.parents('.field').hide();
                } else {
                    arrival_co_traveller_full_name_field.parents('.field').show();
                    arrival_co_traveller_dob_field.parents('.field').show();
                }

                var arrival_do_you_have_quantity_on_hand = arrival_do_you_have_quantity_on_hand_field ? arrival_do_you_have_quantity_on_hand_field.value._latestValue : '';
                if (arrival_do_you_have_quantity_on_hand == "Yes") {
                    $('#arrival_carrying_on_hand').show();
                } else {
                    // reset data
                    var arrData = [];
                    _.each($("#custom-checkout-form").serializeArray(),function(e){
                        if(e.name.slice(-3) == 'api') {
                            arrData.push({'name' : e.name, 'value': 0});
                        }else{
                            arrData.push({'name' : e.name, 'value': e.value});
                        }
                    });

                    $.ajax({
                        url: urlFormatter.build('ecommage_bodak/checkout/index'),
                        method: 'POST',
                        data: {
                            'data': arrData
                        },
                        showLoader: true,
                        success: function (resp) {
                            var deferred = $.Deferred();
                            getTotalsAction([], deferred);
                        }
                    });
                    $('#arrival_carrying_on_hand').hide();
                }

                // $.ajax({
                //     url: urlFormatter.build("/customapi/flightdetails?flight_date=" + arrival_flight_date + "&flight_no=" + arrival_flight_number),
                //
                //     global: false,
                //     contentType: 'application/json',
                //     type: 'GET',
                //     async: true
                // }).done(
                //     function (response) {
                //         console.log(response.Airline);
                //     }
                // ).fail(
                //     function (response) {
                //
                //     }
                // );

                // for auto select region_id
                var region_id = '';
                region_id = $('#shipping-new-address-form select[name="region_id"]').val();
                if (region_id != '') {
                    $('#shipping-new-address-form select[name="region_id"] option').eq(1).prop('selected', true);
                }

            }, 100);
            return this;
        },

        /**
         * Form submit handler
         */
        saveCustomFields: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('customCheckoutForm.data.validate');

            if (!this.source.get('params.invalid')) {
                var formData = this.source.get('customCheckoutForm');

                // for add on hand quantity into quote
                var on_hand_qty_spirit_wine = $('#custom-checkout-form input[name="on_hand_qty_spirit_wine"]').val();
                var on_hand_qty_spirit_wine = $('#custom-checkout-form input[name="on_hand_qty_spirit_wine"]').val();
                var on_hand_qty_beer = $('#custom-checkout-form input[name="on_hand_qty_beer"]').val();
                var on_hand_qty_tobacco = $('#custom-checkout-form input[name="on_hand_qty_tobacco"]').val();
                var on_hand_qty_flv_tobacco = $('#custom-checkout-form input[name="on_hand_qty_flv_tobacco"]').val();

                var arrival_do_you_have_quantity_on_hand_field = uiRegistry.get('index = arrival_do_you_have_quantity_on_hand');
                var arrival_do_you_have_quantity_on_hand = arrival_do_you_have_quantity_on_hand_field.value._latestValue;

                var arrival_quantity_on_hand_arr = [];
                if (arrival_do_you_have_quantity_on_hand == "Yes") {
                    arrival_quantity_on_hand_arr.push(
                        {
                            on_hand_qty_spirit_wine: on_hand_qty_spirit_wine,
                            on_hand_qty_beer: on_hand_qty_beer,
                            on_hand_qty_tobacco: on_hand_qty_tobacco,
                            on_hand_qty_flv_tobacco: on_hand_qty_flv_tobacco
                        }
                    );
                    var arrival_quantity_on_hand = JSON.stringify(arrival_quantity_on_hand_arr);
                    formData['arrival_quantity_on_hand'] = arrival_quantity_on_hand;
                } else {
                    var arrival_quantity_on_hand_arr = [];
                    var arrival_quantity_on_hand = JSON.stringify(arrival_quantity_on_hand_arr);
                    formData['arrival_quantity_on_hand'] = arrival_quantity_on_hand;
                }
                // console.log(formData);

                var quoteId = quote.getQuoteId();
                var isCustomer = customer.isLoggedIn();
                var url;

                if (isCustomer) {
                    url = urlBuilder.createUrl('/carts/mine/set-order-custom-fields', {});
                } else {
                    url = urlBuilder.createUrl('/guest-carts/:cartId/set-order-custom-field', {cartId: quoteId});
                }

                var payload = {
                    cartId: quoteId,
                    customFields: formData
                };
                var result = true;
                $.ajax({
                    url: urlFormatter.build(url),
                    data: JSON.stringify(payload),
                    global: false,
                    contentType: 'application/json',
                    type: 'PUT',
                    async: true
                }).done(
                    function (response) {
                        cartCache.set('custom-form', formData);
                        result = true;
                    }
                ).fail(
                    function (response) {
                        result = false;
                        errorProcessor.process(response);
                    }
                );

                return result;
            }
        },

        /**
         * Trigger updateAfterChange if form is change
         */
        updateAfterChange: function () {
            setTimeout(this.updateTotalAmount, 1000);
        },

        /**
         * updateTotalAmount
         */
        updateTotalAmount: function () {
            // console.log("updateTotalAmount");
            cartCache.set('totals', null);
            defaultTotal.estimateTotals();
        }
    });
});
