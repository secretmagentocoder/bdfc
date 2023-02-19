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
    'Bodak_CheckoutCustomForm/js/model/checkout/departure-checkout-form',
    'Magento_Checkout/js/model/step-navigator'
], function (ko, $, uiRegistry, urlFormatter, Component, customer, quote, urlBuilder, errorProcessor, cartCache, formData,stepNavigator) {
    'use strict';

    return Component.extend({
        departureFields: ko.observable(null),
        formData: formData.departureFieldsData,
        /**
         * Initialize component
         *
         * @returns {exports}
         */
        initialize: function () {
            var self = this;
            this._super();
            formData = this.source.get('departureCheckoutForm');
            var formDataCached = cartCache.get('departure-form');
            if (formDataCached) {
                formData = this.source.set('departureCheckoutForm', formDataCached);
            }

            this.departureFields.subscribe(function (change) {
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

                // call api check airline && validate flight
                // console.log(formData);

                var formFlight = {
                    departure_pick_up_by_another_person: $('body').find('select[name=departure_pick_up_by_another_person]').val(),
                    departure_another_person_name: $('body').find('input[name=departure_another_person_name]').val(),
                    departure_another_person_phone: $('body').find('input[name=departure_another_person_phone]').val(),
                    departure_flight_date: $('body').find('.departure_flight_date-input').val(),
                    departure_flight_number: $('body').find('input[name=departure_flight_number]').val(),
                    departure_collection_time: $('body').find('input[name=departure_collection_time]').val(),
                    departure_allowance_limit: $('body').find('input[name=departure_allowance_limit]').is(':checked'),
                }

                $(document).on('change', '.departure_flight_date-input', function () {
                    formFlight.departure_flight_date = $(this).val();
                    self.onValidateDepartureFlightInfo(formFlight);
                    if ($('input[name="departure_flight_number"]').val() && $(this).val()) {
                        self.checkDepartureApi($(this).val(), $('input[name="departure_flight_number"]').val());
                    }
                });
                $(document).on('change', 'input[name=departure_flight_number]', function () {
                    formFlight.departure_flight_number = $(this).val();
                    self.onValidateDepartureFlightInfo(formFlight);
                    if ($('input[name="departure_flight_date"]').val() && $(this).val()) {
                        self.checkDepartureApi($('input[name="departure_flight_date"]').val(), $(this).val());
                    }
                });

                // gan data -> formData -> call func validate
                $(document).on('change', 'select[name=departure_pick_up_by_another_person]', function () {
                    formFlight.departure_pick_up_by_another_person = $(this).val();
                    self.onValidateDepartureFlightInfo(formFlight);
                });
                $(document).on('change', 'input[name=departure_collection_time]', function () {
                    formFlight.departure_collection_time = $(this).val();
                    self.onValidateDepartureFlightInfo(formFlight);
                });
                $(document).on('change', 'input[name=departure_allowance_limit]', function () {
                    formFlight.departure_allowance_limit = $(this).is(':checked');
                    self.onValidateDepartureFlightInfo(formFlight);
                });
                $(document).on('change', 'input[name=departure_another_person_name]', function () {
                    formFlight.departure_another_person_name = $(this).val();
                    self.onValidateDepartureFlightInfo(formFlight);
                });
                $(document).on('change', 'input[name=departure_another_person_phone]', function () {
                    formFlight.departure_another_person_phone = $(this).val();
                    self.onValidateDepartureFlightInfo(formFlight);
                });

                // onValidate when click continue
                $('body').on('click', '.actions-toolbar button[data-role=opc-continue-departure-delivery]', function (e) {
                    if(quote.getStoreCode() == 'departure'){
                        var formFlight = {
                            departure_select_your_lounge: $('body').find('select[name=departure_select_your_lounge]').val(),
                            departure_pick_up_by_another_person: $('body').find('select[name=departure_pick_up_by_another_person]').val(),
                            departure_another_person_name: $('body').find('input[name=departure_another_person_name]').val(),
                            departure_another_person_phone: $("#opc-departure-checkout-form .iti__selected-flag .iti__selected-dial-code").text()+'-'+$('body').find('input[name=departure_another_person_phone]').val(),
                            departure_flight_date: $('body').find('.departure_flight_date-input').val(),
                            departure_flight_number: $('body').find('input[name=departure_flight_number]').val(),
                            departure_collection_time: $('body').find('input[name=departure_collection_time]').val(),
                            departure_allowance_limit: $('body').find('input[name=departure_allowance_limit]').is(':checked'),
                        }

                        if(!self.onValidateDepartureFlightInfo(formFlight)){
                            e.preventDefault();
                            if (!$('#opc-departure-checkout-form div:last-child').hasClass('field-error')) {
                                $('body').find('#opc-departure-checkout-form').append('<div class="field-error"><span>Please fill all required fields</span></div>');
                            }
                        }else{
                            self.setDepartureCheckoutSession(formFlight);
                            if ($('div[name="departureCheckoutForm.departure_flight_number"] div:last-child').hasClass('field-error')) {
                                e.preventDefault();
                                $('.opc-departure-checkout-form').find('.field-error').remove();
                                // $('.button-flight-next-to-delivery').append('<div class="field-error flight-infomation"><span>Invalid flight information</span></div>');
                            }else{
                                $('#opc-departure-checkout-form').find('.field-error').remove();

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

                                    //active button next to payment form shipping core
                                    // $('li#opc-shipping_method').show();
                                }else{
                                    // next to payment 
                                    $("button[data-trigger=continue-payment]").trigger("click");
                                }
                            }
                        }
                    }

                });
            });

            return this;
        },

        /**
         * Check if customer is logged in
         *
         * @return {boolean}
         */
        isLoggedIn: function () {
            return customer.isLoggedIn();
        },

        nextShipping : function () {
        if(quote.getStoreCode() != 'departure') {
                stepNavigator.next();
            }
        },

        /**
         * Trigger save method if form is change
         */
        onFormChange: function (value) {
            // console.log("sds");
            this.fieldDepend(value);

            this.saveDepartureFields();
        },

        /*
         * validate required form flight infomation
         * */

        onValidateDepartureFlightInfo: function (formData) {
            var flag = true;
            if (formData.departure_pick_up_by_another_person == 'Pick Up By Another Person') {
                if (!formData.departure_another_person_name || !formData.departure_another_person_phone || !formData.departure_collection_time || !formData.departure_flight_number || !formData.departure_flight_date) {
                    flag = false;
                } else {
                    flag = true;
                }
            } else {
                if (!formData.departure_collection_time || !formData.departure_flight_number || !formData.departure_flight_date
                ) {
                    flag = false;
                } else {
                    flag = true;
                }
            }
            
            return flag;
        },

        /*
         * set checkout session
         * */
        setDepartureCheckoutSession: function (formFlight) {
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

        /*
         * call api + render html airline infomation
         *  */
        checkDepartureApi: function (departureDate, flightNo) {
            var ajaxUrl = 'ecommage_checkoutdata/ajax/departure';
            var date = departureDate.replace('/', '-').replace('/', '-');

            $.ajax({
                url: urlFormatter.build(ajaxUrl),
                dataType: 'json',
                data: {
                    'departureDate': date,
                    'flightNo': flightNo
                },
                showLoader: true,
                success: function (res) {
                    if (res) {
                        $('.airline_logo').attr('src', res.airline_logo);
                        $('.airline_name').html(res.airline_name);
                        $('.departure_time').html(res.departure_time);
                        $('#departure_flight_destination').html(res.destination);
                    
                        $('.airline_logo').show();
                        $('#departure-flight-details').show();
                        $('div[name="departureCheckoutForm.departure_flight_number"]').find('.field-error').remove();
                    
                        $('#checkout-step-shipping_method').find('.field-error .flight-infomation').remove();
                        $('body').find('.button-flight-next-to-delivery button').attr('disabled',false);
                    } else {
                        $('#departure-flight-details').hide();
                        $('.airline_logo').show();
                        $('.airline_logo').hide();
                    
                        if (!$('div[name="departureCheckoutForm.departure_flight_number"] div:last-child').hasClass('field-error')) {
                            $('div[name="departureCheckoutForm.departure_flight_number"]').append('<div class="field-error"><span>Invalid flight information</span></div>');

                            $('body').find('.button-flight-next-to-delivery button').attr('disabled',true);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error)
                }
            })
        },

        /**
         * Update field dependency
         *
         * @param {String} value
         */
        fieldDepend: function (value) {
            // console.log("sds");
            setTimeout(function () {
                var departure_collection_time_field = $('#departure-checkout-form select[name="departure_collection_time"]');
                var departure_pick_up_by_another_person_field = $('#departure-checkout-form select[name="departure_pick_up_by_another_person"]');
                var departure_another_person_name_field = $('#departure-checkout-form input[name="departure_another_person_name"]');
                var departure_another_person_phone_field = $('#departure-checkout-form input[name="departure_another_person_phone"]');
                var departure_destination_field = $('#departure-checkout-form select[name="departure_destination"]');
                var departure_connected_destination_field = $('#departure-checkout-form select[name="departure_connected_destination"]');
                var departure_is_direct_or_connecting_flight_field = uiRegistry.get('index = departure_is_direct_or_connecting_flight');

                var departure_pick_up_by_another_person = departure_pick_up_by_another_person_field.val();
                if (departure_pick_up_by_another_person == "Pick Up By Another Person") {
                    departure_another_person_name_field.parents('.field').show();
                    departure_another_person_phone_field.parents('.field').show();
                } else {
                    departure_another_person_name_field.parents('.field').hide();
                    departure_another_person_phone_field.parents('.field').hide();
                }

                // var departure_is_direct_or_connecting_flight = departure_is_direct_or_connecting_flight_field.value._latestValue;
                // if (departure_is_direct_or_connecting_flight == "Connected Flight") {
                //     departure_destination_field.parents('.field').show();
                //     departure_connected_destination_field.parents('.field').show();
                // } else {
                //     departure_destination_field.parents('.field').hide();
                //     departure_connected_destination_field.parents('.field').hide();
                // }

                // default value
                // $('.shipping-address-delivery input[name="street[0]"]').val("Pickup from Departure Store");
                // $('.shipping-address-delivery select[name="region_id"]').val("569");
                // $('.shipping-address-delivery input[name="city"]').val("Departure Store");
                // $('.shipping-address-delivery input[name="postcode"]').val("323");

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
        saveDepartureFields: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('departureCheckoutForm.data.validate');

            if (!this.source.get('params.invalid')) {
                var formData = this.source.get('departureCheckoutForm');
                var quoteId = quote.getQuoteId();
                var isCustomer = customer.isLoggedIn();
                var url;

                if (isCustomer) {
                    url = urlBuilder.createUrl('/carts/mine/set-order-departure-fields', {});
                } else {
                    url = urlBuilder.createUrl('/guest-carts/:cartId/set-order-departure-field', {cartId: quoteId});
                }

                var payload = {
                    cartId: quoteId,
                    departureFields: formData
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
                        cartCache.set('departure-form', formData);
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
        }

    });
});
