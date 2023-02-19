
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'mage/url',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    url,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    customerData,
    registry,
    $t
) {
    'use strict';

    var countryData = customerData.get('directory-data');

    var mixin = {

        defaults: {
            template: 'Bodak_CheckoutCustomForm/shipping'
        },
         getCustomerStreet:  function(){
            let data = window.checkoutConfig.streetData ? window.checkoutConfig.streetData : window.checkoutConfig.streetLines ;
            return data;
        },

        getCustomerDeliveryAt: function(){
            let addresses = window.checkoutConfig.customerData.addresses ? window.checkoutConfig.customerData.addresses : [];
            let deliveryAt = '';
            _.each(addresses,function(value,index){
                if(value.default_shipping){
                    deliveryAt = value.custom_attributes.deliver_at.value;
                }
            })
            return deliveryAt;
        },

        nextShipping: function () {
            // check validate required field in departure && arrival store case Only virtual
            // validate step delivery in store departure case not login + onlyVirtual
                if(this.onlyVirtual()){
                    if(quote.getStoreCode() != 'home_delivery'){
                        var street = $('#opc-delivery-checkout-form input[name="street[]"]').map(function(){return $(this).val();}).get();
                        var validate  = this.validateStreet(street);
                        if(validate == 1 && !$('li#opc-delivery-checkout-form .co-step-content div:last-child').hasClass('field-error')){
                            $('li#opc-delivery-checkout-form .co-step-content').append('<div class="field-error delivery-infomation"><span>Please fill all required fields</span></div>');
                            return false;
                        }
                        if(validate != 1){
                            $('li#opc-delivery-checkout-form .co-step-content').find('.field-error').remove();
                            stepNavigator.next();
                        }
                    }else{
                        //store home_delivery
                        if(!this.isLoggedIn()){
                            var street = $('.delivery-form input[name="street[]"]').map(function(){return $(this).val();}).get();
                            
                        }else{
                            var street = $('#custom-shipping-new-address-form input[name="street[]"]').map(function(){return $(this).val();}).get();
                            
                        }
                        var validate  = this.validateStreet(street);
                            if(validate == 1){
                                $('.delivery-detail-infomation-response-not-login').html('<div class="field-error delivery-infomation"><span>Please fill all required fields</span></div>');
                                $('#custom-shipping-new-address-form').find('.delivery_response-step').html('<div class="field-error delivery-infomation"><span>Please fill all required fields</span></div>');
                                return false;
                            }
                            if(validate != 1){
                                $('#custom-shipping-new-address-form').find('.delivery_response-step').find('.field-error').remove()
                                $('.delivery-detail-infomation-response-not-login').find('.field-error').remove();
                                stepNavigator.next();
                            }
                    }
                }
        },

        // check step 2 store
        checkVisibleStepFlight: function(){
            return quote.isVirtual();
        },

        productSimpleVirtualExist: function (){
            var simple = '';
            var virtual = '';
            if (quote.getStoreCode() != 'home_delivery') {
                let quoteData = window.checkoutConfig.quoteItemData;
                _.each(quoteData,function(val,key){
                    if(val.is_virtual == 1){
                       virtual = 1;
                    }
                    if(val.is_virtual == 0){
                        simple = 1;
                    }
                });

                if(simple && virtual){
                    return true;
                }
                return false;

            }
        },

        // only simple-> not visible delivery
        checkVisibleStepDelivery: function(){
            var flag = 'product';
            if (quote.getStoreCode() != 'home_delivery') {
                let quoteData = window.checkoutConfig.quoteItemData;
                _.each(quoteData,function(val,key){
                    if(val.is_virtual == 1){
                        flag = 'product_raffle';
                        return flag;
                    }
                });

            }
            return flag;
        },

        onlyVirtual: function(){
            return quote.isVirtual();
        },

        validateStreet : function (arr) {
            var i = '';
            $.each(arr , function (key,value) {
                if (!value){
                   i = 1;
                }
            })
            return i;
        },

        /**
         * Check if customer is logged in
         *
         * @return {boolean}
         */
        isLoggedIn: function () {
            return customer.isLoggedIn();
        },

        /**
         * Show arrival form
         */
        arrival_form_visible: function () {
            // console.log(quote.getItems());
            var current_store = quote.getStoreCode();
            if (current_store == 'arrival' && this.getIsVirtual() != 1 ) {
                return true;
            }

            return false;
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
         * @param {String} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            countryId = countryId.toUpperCase();
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        /**
         * Show home_delivery form
         */
        home_arrival_form_visible_show: function () {
             var text = '';
            if (!this.getIsVirtual() && quote.getStoreCode() != 'home_delivery') {
                text = 'display:none';
            }
            return text;
        },

         hide_not_virtual_from_home: function(){
            var text = '';
            if (!this.getIsVirtual() && quote.getStoreCode() == 'home_delivery') {
                text = 'display:none';
            }
            return text;
        },

        /**
         * Show home_delivery form
         */
        home_delivery_form_visible_hide: function () {
            var text = 'display:block';
            var current_store = quote.getStoreCode();
            if (current_store == 'home_delivery') {
                text = 'display:none';
            }
            return text;
        },


        /**
         * Show departure form
         */
        departure_form_visible: function () {
            // console.log(quote.getStoreCode());
            var current_store = quote.getStoreCode();
            if (current_store == 'departure' && this.getIsVirtual() != 1) {
                return true;
            }

            return false;
        },

        /**
         * Show home_delivery form
         */
        home_delivery_form_visible: function () {
            // console.log(quote.getStoreCode());
            var current_store = quote.getStoreCode();
            if (current_store == 'home_delivery') {
                return true;
            }

            return false;
        },

        setBillingAddress: function(data){
            $.ajax({
                url: url.build('ecommage_bodak/checkout/setquote'),
                method: 'GET',
                data: data 
                ,
                showLoader: false,
                success: function (resp) {
                    console.log(resp);
                }
            });
        },

        /**
        * @returns void
        */
        setPersonalInformation: function () {
            var firstname_field = $('#shipping-new-address-form input[name="firstname"]');
            var lastname_field = $('#shipping-new-address-form input[name="lastname"]');
            var dob_field = $('#shipping-new-address-form input[name="cust_dob"]');
            var country_id_field = $('#shipping-new-address-form input[name="country_customer"]');
            var nationality_field = $('#shipping-new-address-form select[name="nationality"]');
            var telephone_field = $('#shipping-new-address-form input[name="mobile_number"]');
            var username_field = $('#shipping-new-address-form input[name="email"]');
            var passport_field = $('#shipping-new-address-form input[name="passport_no"]');
            var firstname = firstname_field.val();
            var lastname = lastname_field.val();
            var dob = dob_field.val();
            var country_id = window.checkoutConfig.storeCode != 'home_delivery' ? country_id_field.val() : true;
            var nationality = nationality_field.val();
            var telephone = telephone_field.val();
            var username = username_field.val();
            var passport = window.checkoutConfig.storeCode != 'home_delivery' ? passport_field.val() : true;

            var firstname_temp, lastname_temp, dob_temp, country_id_temp, nationality_temp, telephone_temp,
                username_temp, username_error_temp, passport_temp;
            // firstname
            if (firstname_field.parent().parent().hasClass('_required') && firstname == '') {
                firstname_field.parent().parent().addClass('_error');
                firstname_temp = '1';
            }
            else {
                firstname_field.parent().parent().removeClass('_error');
                firstname_temp = '0';
            }
            // lastname
            if (lastname_field.parent().parent().hasClass('_required') && lastname == '') {
                lastname_field.parent().parent().addClass('_error');
                lastname_temp = '1';
            }
            else {
                lastname_field.parent().parent().removeClass('_error');
                lastname_temp = '0';
            }
            // country_id
            if (country_id_field.parent().parent().hasClass('_required') && country_id == '') {
                country_id_field.parent().parent().addClass('_error');
                country_id_temp = '1';
            } else {
                country_id_field.parent().parent().removeClass('_error');
                country_id_temp = '0';
            }

            if (nationality_field.parent().parent().hasClass('_required') && nationality == '') {
                nationality_field.parent().parent().addClass('_error');
                nationality_temp = '1';
            } else {
                nationality_field.parent().parent().removeClass('_error');
                nationality_temp = '0';
            }
            // telephone
            if (telephone_field.parent().parent().hasClass('_required') && telephone == '') {
                telephone_field.parent().parent().addClass('_error');
                telephone_temp = '1';
            }
            else {
                telephone_field.parent().parent().removeClass('_error');
                telephone_temp = '0';
            }
            // username
            if (username_field.parent().parent().hasClass('required') && username == '') {
                username_field.parent().parent().addClass('_error');
                username_temp = '1';
            } else if (checkEmail(username) == false) {
                username_field.parent().parent().addClass('_error');
                username_temp = '1';
            } else {
                username_field.parent().parent().removeClass('_error');
                username_temp = '0';
            }
            // username_error_temp
            if (username_field.hasClass('mage-error')) {
                username_error_temp = '1';
            } else {
                username_error_temp = '0';
            }

            var isLoggedIn = customer.isLoggedIn();
            if (isLoggedIn == true) {
                username_temp = '0';
                username_error_temp = '0';
            }

            if (firstname_temp == '1' || lastname_temp == '1' || dob_temp == '1' || country_id_temp == '1' || telephone_temp == '1' || username_temp == '1' || username_error_temp == '1' || nationality_temp == 1 || !passport) {
                $('.personal_information_response').html("Please fill all required fields").addClass("error");
                return false;
            } else {
                var formData = $('form#co-shipping-form').serializeArray();
                this.setBillingAddress(formData);
                $('.personal_information_response').html("").removeClass("error");

                // only virtual->next to step delivery
                if(this.onlyVirtual()){
                    $('.custom-checkout-step').find('.co-step-title').removeClass("active");
                    $('.custom-checkout-step').find('.co-step-content').removeClass("active");
                    $('.custom-checkout-step').find('.co-step-title').removeClass("prev_step");
                    $('.shipping-address-personal').find('.co-step-title').addClass("prev_step");

                    $('#opc-delivery-checkout-form .co-step-title').addClass('active');
                    $('#opc-delivery-checkout-form .co-step-content').addClass('active');
                }
                if ($('#shipping-new-address-form').parents('.modal-content').length) {
                    //
                    $('#opc-new-shipping-address .custom-checkout-step').find('.co-step-title').removeClass("active");
                    $('#opc-new-shipping-address .custom-checkout-step').find('.co-step-content').removeClass("active");

                    $('#opc-new-shipping-address .custom-checkout-step').find('.co-step-title').removeClass("prev_step");
                    $('#opc-new-shipping-address .shipping-address-personal').find('.co-step-title').addClass("prev_step");

                    $('#opc-new-shipping-address .shipping-address-delivery').find('.co-step-title').addClass("active");
                    $('#opc-new-shipping-address .shipping-address-delivery').find('.co-step-content').addClass("active");

                    $('#opc-new-shipping-address .shipping-address-delivery').show();

                } 
                else {
                    var current_store = quote.getStoreCode();
                    $('.custom-checkout-step').find('.co-step-title').removeClass("active");
                    $('.custom-checkout-step').find('.co-step-content').removeClass("active");

                    $('.custom-checkout-step').find('.co-step-title').removeClass("prev_step");
                    $('.shipping-address-personal').find('.co-step-title').addClass("prev_step");
                    if (current_store == 'home_delivery') {

                        $('.shipping-address-delivery').find('.co-step-title').addClass("active");
                        $('.shipping-address-delivery').find('.co-step-content').addClass("active");

                        $('.checkout-shipping-method').addClass("active");
                        $('.checkout-shipping-method').addClass("visible");
                    } else {
                        // check 2 case only product or only raffle
                        if(current_store == 'departure' && !this.checkVisibleStepFlight()){
                            $('#opc-departure-checkout-form').find('.co-step-content').addClass('active');
                            $('#opc-departure-checkout-form').find('.co-step-title').addClass('active');
                        }
                        if(current_store == 'departure' && this.onlyVirtual()){
                            $('#opc-delivery-checkout-form').find('.co-step-content').addClass('active');
                            $('#opc-delivery-checkout-form').find('.co-step-title').addClass('active');
                            $('.checkout-shipping-method').addClass("active");
                            $('.checkout-shipping-method').removeClass("visible");
                        }

                        if(current_store == 'arrival' && !this.checkVisibleStepFlight()){
                            $('li#opc-custom-checkout-form').find('.co-step-content').addClass('active');
                            $('li#opc-custom-checkout-form').find('.co-step-title').addClass('active');
                        }
                        if(current_store == 'arrival' && this.onlyVirtual()){
                            $('#opc-delivery-checkout-form').find('.co-step-content').addClass('active');
                            $('#opc-delivery-checkout-form').find('.co-step-title').addClass('active');
                            $('.checkout-shipping-method').addClass("active");
                            $('.checkout-shipping-method').removeClass("visible");
                        }
                    }
                }

            }

            return false;
        },

        /**
        * @returns void
        */
        showPrevStep: function (item, event) {
            if ($(event.target).hasClass("prev_step")) {
                $('.custom-checkout-step').find('.co-step-title').removeClass("active");
                $('.custom-checkout-step').find('.co-step-content').removeClass("active");
                $('.custom-checkout-step').find('.co-step-title').removeClass("prev_step");

                $(event.target).parent().find('.co-step-title').addClass("active");
                $(event.target).parent().find('.co-step-content').addClass("active");

                $('.checkout-shipping-method').removeClass("visible");
                $('.checkout-shipping-method').removeClass("active");
            }
        },

        prevStepFlightArrival: function (){
            if($('#opc-custom-checkout-form').find('.co-step-title').hasClass('prev_step')){
                $('#opc-custom-checkout-form').find('.co-step-title').removeClass('prev_step')
                $('#opc-custom-checkout-form').find('.co-step-title').addClass('active');
                $('#opc-custom-checkout-form').find('.co-step-content').addClass('active');
                
                $('#opc-delivery-checkout-form').find('.co-step-content').removeClass('active');
                $('#opc-delivery-checkout-form').find('.co-step-title').removeClass('active');
              
                $('.checkout-shipping-method').removeClass("visible");
                $('.checkout-shipping-method').removeClass("active");

                $('body').find('#payment').hide();
            }
        },


        /**
        * @returns void
        */
        getCustomerEmail: function() {
            // console.log("sds");
            var customerData = customer.customerData;
            var customer_email = customerData.email;

            return customer_email;
        }

    };

    return function (target) {
        return target.extend(mixin);
    };


    // checkUsername
    function checkUsername(username){
        var pattern = /^[a-zA-Z]{2,10}$/;
        if(pattern.test(username)){
            return true;
        }else{
            return false;
        }
    }

    // checkDateOfBirth
    function checkDateOfBirth(dob){
        var pattern = /^[0-9]{2}[/][0-9]{2}[/][0-9]{4}$/;
        if(pattern.test(dob)){
            return true;
        }else{
            return false;
        }
    }

    // checkMobileNumber
    function checkMobileNumber(mobile){
        var pattern = /^[0-9]{10}$/;
        if(pattern.test(mobile)){
            return true;
        }else{
            return false;
        }
    }

    // checkEmail
    function checkEmail(email){
        // var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
        var pattern = new RegExp(/^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,7}\b$/i);
        if(pattern.test(email)){
            return true;
        }else{
            return false;
        }
    }

});
