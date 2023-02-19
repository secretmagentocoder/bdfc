
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
    var customerCheckoutData = customerData.get('checkout-data');
    var customerMobileNumber = customerData.get('mobile-number');
    var passportNumber = customerData.get('passport_no');
    let mobileCode = customerData.get('mobile-code');
    let nationality = customerData.get('nationality');
    let residingPlace = customerData.get('residing-country');
    
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

        checkAddressAvailable: function () {
            var isLoggedIn = customer.isLoggedIn();
            if (isLoggedIn == true) {
                if (! $.isEmptyObject(window.checkoutConfig.customerData.addresses)) {
                    return true;
                }
            }
            return false;
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
                data: data,
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

                let mobileCode = $('#country_code').val();
                var countryCode = $(".shipping-address-personal .mobile-number .iti__selected-flag .iti__selected-dial-code").text();
                $('#dial_code').val(countryCode);
                countryCode = getCountryByPh(countryCode);
                if (mobileCode != countryCode) {
                    $('#country_code').val(countryCode);
                }

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
            var customerData = customer.customerData;
            var customer_email = customerData.email;
            if (!customer_email) {
                customer_email = customerCheckoutData().inputFieldEmailValue;
            }
            return customer_email;
        },

        getCustomerMobile: function() {
            if (customer.isLoggedIn()) {
                return window.checkoutConfig.customerData.custom_attributes.mobile_number ? window.checkoutConfig.customerData.custom_attributes.mobile_number.value:'';
            }
            if (typeof customerMobileNumber() === 'object') {
                return null;
            }
            return customerMobileNumber;
          
        },

        getMobileCountryCode: function() {           
            if (customer.isLoggedIn()) {
                return window.checkoutConfig.customerData.custom_attributes.country_code ? window.checkoutConfig.customerData.custom_attributes.country_code.value:'';          
            }
            if (typeof mobileCode() === 'object') {
                return "BH";
            }
            return mobileCode;
        },

        getPassportNumber: function() {
            if (customer.isLoggedIn()) {
                return window.checkoutConfig.customerData.custom_attributes.passport_no ? window.checkoutConfig.customerData.custom_attributes.passport_no.value:'';
            }
            if (typeof passportNumber() === 'object') {
                return null;
            }
            return passportNumber;
        },

        getNational: function() {
            if (customer.isLoggedIn()) {
                return window.checkoutConfig.customerData.custom_attributes.national_id ? window.checkoutConfig.customerData.custom_attributes.national_id.value:'';
            }
            if (typeof nationality() === 'object') {
                return null;
            }
            return nationality;
        },

        getResidingCountry: function() {
            if (typeof residingPlace() === 'object') {
                return null;
            }
            return residingPlace;
        },


        /**
        * @returns void
        */
        setValuestoCookie: function(event) {
            let nationality = $('#opption-select').val();
            let residingCountry = $('#country_customer').val();
            let mobileNumber = $('#mobile_number').val();
            let mobileCode = $('#country_code').val();
            let dob = $('#customer_dob').val();
            let passportNo = $('#passport_no').val();
            var countryCode = $(".shipping-address-personal .mobile-number .iti__selected-flag .iti__selected-dial-code").text();
            $('#dial_code').val(countryCode);
            countryCode = getCountryByPh(countryCode);
            if (mobileCode != countryCode) {
                $('#country_code').val(countryCode);
            } 
            customerData.set('mobile-number', countryCode+mobileNumber);
            customerData.set('dob', dob);
            customerData.set('passport_no', passportNo);
            customerData.set('seclected-nationality', nationality);
            customerData.set('mobile-code', mobileCode);
            customerData.set('nationality', nationality);
            customerData.set('residing-country', residingCountry);
        }

    };

    function getCountryByPh(ph) {
        var allCountries = [ [ "Afghanistan (‫افغانستان‬‎)", "af", "93" ], [ "Albania (Shqipëri)", "al", "355" ], [ "Algeria (‫الجزائر‬‎)", "dz", "213" ], [ "American Samoa", "as", "1", 5, [ "684" ] ], [ "Andorra", "ad", "376" ], [ "Angola", "ao", "244" ], [ "Anguilla", "ai", "1", 6, [ "264" ] ], [ "Antigua and Barbuda", "ag", "1", 7, [ "268" ] ], [ "Argentina", "ar", "54" ], [ "Armenia (Հայաստան)", "am", "374" ], [ "Aruba", "aw", "297" ], [ "Australia", "au", "61", 0 ], [ "Austria (Österreich)", "at", "43" ], [ "Azerbaijan (Azərbaycan)", "az", "994" ], [ "Bahamas", "bs", "1", 8, [ "242" ] ], [ "Bahrain (‫البحرين‬‎)", "bh", "973" ], [ "Bangladesh (বাংলাদেশ)", "bd", "880" ], [ "Barbados", "bb", "1", 9, [ "246" ] ], [ "Belarus (Беларусь)", "by", "375" ], [ "Belgium (België)", "be", "32" ], [ "Belize", "bz", "501" ], [ "Benin (Bénin)", "bj", "229" ], [ "Bermuda", "bm", "1", 10, [ "441" ] ], [ "Bhutan (འབྲུག)", "bt", "975" ], [ "Bolivia", "bo", "591" ], [ "Bosnia and Herzegovina (Босна и Херцеговина)", "ba", "387" ], [ "Botswana", "bw", "267" ], [ "Brazil (Brasil)", "br", "55" ], [ "British Indian Ocean Territory", "io", "246" ], [ "British Virgin Islands", "vg", "1", 11, [ "284" ] ], [ "Brunei", "bn", "673" ], [ "Bulgaria (България)", "bg", "359" ], [ "Burkina Faso", "bf", "226" ], [ "Burundi (Uburundi)", "bi", "257" ], [ "Cambodia (កម្ពុជា)", "kh", "855" ], [ "Cameroon (Cameroun)", "cm", "237" ], [ "Canada", "ca", "1", 1, [ "204", "226", "236", "249", "250", "289", "306", "343", "365", "387", "403", "416", "418", "431", "437", "438", "450", "506", "514", "519", "548", "579", "581", "587", "604", "613", "639", "647", "672", "705", "709", "742", "778", "780", "782", "807", "819", "825", "867", "873", "902", "905" ] ], [ "Cape Verde (Kabu Verdi)", "cv", "238" ], [ "Caribbean Netherlands", "bq", "599", 1, [ "3", "4", "7" ] ], [ "Cayman Islands", "ky", "1", 12, [ "345" ] ], [ "Central African Republic (République centrafricaine)", "cf", "236" ], [ "Chad (Tchad)", "td", "235" ], [ "Chile", "cl", "56" ], [ "China (中国)", "cn", "86" ], [ "Christmas Island", "cx", "61", 2 ], [ "Cocos (Keeling) Islands", "cc", "61", 1 ], [ "Colombia", "co", "57" ], [ "Comoros (‫جزر القمر‬‎)", "km", "269" ], [ "Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)", "cd", "243" ], [ "Congo (Republic) (Congo-Brazzaville)", "cg", "242" ], [ "Cook Islands", "ck", "682" ], [ "Costa Rica", "cr", "506" ], [ "Côte d’Ivoire", "ci", "225" ], [ "Croatia (Hrvatska)", "hr", "385" ], [ "Cuba", "cu", "53" ], [ "Curaçao", "cw", "599", 0 ], [ "Cyprus (Κύπρος)", "cy", "357" ], [ "Czech Republic (Česká republika)", "cz", "420" ], [ "Denmark (Danmark)", "dk", "45" ], [ "Djibouti", "dj", "253" ], [ "Dominica", "dm", "1", 13, [ "767" ] ], [ "Dominican Republic (República Dominicana)", "do", "1", 2, [ "809", "829", "849" ] ], [ "Ecuador", "ec", "593" ], [ "Egypt (‫مصر‬‎)", "eg", "20" ], [ "El Salvador", "sv", "503" ], [ "Equatorial Guinea (Guinea Ecuatorial)", "gq", "240" ], [ "Eritrea", "er", "291" ], [ "Estonia (Eesti)", "ee", "372" ], [ "Ethiopia", "et", "251" ], [ "Falkland Islands (Islas Malvinas)", "fk", "500" ], [ "Faroe Islands (Føroyar)", "fo", "298" ], [ "Fiji", "fj", "679" ], [ "Finland (Suomi)", "fi", "358", 0 ], [ "France", "fr", "33" ], [ "French Guiana (Guyane française)", "gf", "594" ], [ "French Polynesia (Polynésie française)", "pf", "689" ], [ "Gabon", "ga", "241" ], [ "Gambia", "gm", "220" ], [ "Georgia (საქართველო)", "ge", "995" ], [ "Germany (Deutschland)", "de", "49" ], [ "Ghana (Gaana)", "gh", "233" ], [ "Gibraltar", "gi", "350" ], [ "Greece (Ελλάδα)", "gr", "30" ], [ "Greenland (Kalaallit Nunaat)", "gl", "299" ], [ "Grenada", "gd", "1", 14, [ "473" ] ], [ "Guadeloupe", "gp", "590", 0 ], [ "Guam", "gu", "1", 15, [ "671" ] ], [ "Guatemala", "gt", "502" ], [ "Guernsey", "gg", "44", 1, [ "1481", "7781", "7839", "7911" ] ], [ "Guinea (Guinée)", "gn", "224" ], [ "Guinea-Bissau (Guiné Bissau)", "gw", "245" ], [ "Guyana", "gy", "592" ], [ "Haiti", "ht", "509" ], [ "Honduras", "hn", "504" ], [ "Hong Kong (香港)", "hk", "852" ], [ "Hungary (Magyarország)", "hu", "36" ], [ "Iceland (Ísland)", "is", "354" ], [ "India (भारत)", "in", "91" ], [ "Indonesia", "id", "62" ], [ "Iran (‫ایران‬‎)", "ir", "98" ], [ "Iraq (‫العراق‬‎)", "iq", "964" ], [ "Ireland", "ie", "353" ], [ "Isle of Man", "im", "44", 2, [ "1624", "74576", "7524", "7924", "7624" ] ], [ "Israel (‫ישראל‬‎)", "il", "972" ], [ "Italy (Italia)", "it", "39", 0 ], [ "Jamaica", "jm", "1", 4, [ "876", "658" ] ], [ "Japan (日本)", "jp", "81" ], [ "Jersey", "je", "44", 3, [ "1534", "7509", "7700", "7797", "7829", "7937" ] ], [ "Jordan (‫الأردن‬‎)", "jo", "962" ], [ "Kazakhstan (Казахстан)", "kz", "7", 1, [ "33", "7" ] ], [ "Kenya", "ke", "254" ], [ "Kiribati", "ki", "686" ], [ "Kosovo", "xk", "383" ], [ "Kuwait (‫الكويت‬‎)", "kw", "965" ], [ "Kyrgyzstan (Кыргызстан)", "kg", "996" ], [ "Laos (ລາວ)", "la", "856" ], [ "Latvia (Latvija)", "lv", "371" ], [ "Lebanon (‫لبنان‬‎)", "lb", "961" ], [ "Lesotho", "ls", "266" ], [ "Liberia", "lr", "231" ], [ "Libya (‫ليبيا‬‎)", "ly", "218" ], [ "Liechtenstein", "li", "423" ], [ "Lithuania (Lietuva)", "lt", "370" ], [ "Luxembourg", "lu", "352" ], [ "Macau (澳門)", "mo", "853" ], [ "Macedonia (FYROM) (Македонија)", "mk", "389" ], [ "Madagascar (Madagasikara)", "mg", "261" ], [ "Malawi", "mw", "265" ], [ "Malaysia", "my", "60" ], [ "Maldives", "mv", "960" ], [ "Mali", "ml", "223" ], [ "Malta", "mt", "356" ], [ "Marshall Islands", "mh", "692" ], [ "Martinique", "mq", "596" ], [ "Mauritania (‫موريتانيا‬‎)", "mr", "222" ], [ "Mauritius (Moris)", "mu", "230" ], [ "Mayotte", "yt", "262", 1, [ "269", "639" ] ], [ "Mexico (México)", "mx", "52" ], [ "Micronesia", "fm", "691" ], [ "Moldova (Republica Moldova)", "md", "373" ], [ "Monaco", "mc", "377" ], [ "Mongolia (Монгол)", "mn", "976" ], [ "Montenegro (Crna Gora)", "me", "382" ], [ "Montserrat", "ms", "1", 16, [ "664" ] ], [ "Morocco (‫المغرب‬‎)", "ma", "212", 0 ], [ "Mozambique (Moçambique)", "mz", "258" ], [ "Myanmar (Burma) (မြန်မာ)", "mm", "95" ], [ "Namibia (Namibië)", "na", "264" ], [ "Nauru", "nr", "674" ], [ "Nepal (नेपाल)", "np", "977" ], [ "Netherlands (Nederland)", "nl", "31" ], [ "New Caledonia (Nouvelle-Calédonie)", "nc", "687" ], [ "New Zealand", "nz", "64" ], [ "Nicaragua", "ni", "505" ], [ "Niger (Nijar)", "ne", "227" ], [ "Nigeria", "ng", "234" ], [ "Niue", "nu", "683" ], [ "Norfolk Island", "nf", "672" ], [ "North Korea (조선 민주주의 인민 공화국)", "kp", "850" ], [ "Northern Mariana Islands", "mp", "1", 17, [ "670" ] ], [ "Norway (Norge)", "no", "47", 0 ], [ "Oman (‫عُمان‬‎)", "om", "968" ], [ "Pakistan (‫پاکستان‬‎)", "pk", "92" ], [ "Palau", "pw", "680" ], [ "Palestine (‫فلسطين‬‎)", "ps", "970" ], [ "Panama (Panamá)", "pa", "507" ], [ "Papua New Guinea", "pg", "675" ], [ "Paraguay", "py", "595" ], [ "Peru (Perú)", "pe", "51" ], [ "Philippines", "ph", "63" ], [ "Poland (Polska)", "pl", "48" ], [ "Portugal", "pt", "351" ], [ "Puerto Rico", "pr", "1", 3, [ "787", "939" ] ], [ "Qatar (‫قطر‬‎)", "qa", "974" ], [ "Réunion (La Réunion)", "re", "262", 0 ], [ "Romania (România)", "ro", "40" ], [ "Russia (Россия)", "ru", "7", 0 ], [ "Rwanda", "rw", "250" ], [ "Saint Barthélemy", "bl", "590", 1 ], [ "Saint Helena", "sh", "290" ], [ "Saint Kitts and Nevis", "kn", "1", 18, [ "869" ] ], [ "Saint Lucia", "lc", "1", 19, [ "758" ] ], [ "Saint Martin (Saint-Martin (partie française))", "mf", "590", 2 ], [ "Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)", "pm", "508" ], [ "Saint Vincent and the Grenadines", "vc", "1", 20, [ "784" ] ], [ "Samoa", "ws", "685" ], [ "San Marino", "sm", "378" ], [ "São Tomé and Príncipe (São Tomé e Príncipe)", "st", "239" ], [ "Saudi Arabia (‫المملكة العربية السعودية‬‎)", "sa", "966" ], [ "Senegal (Sénégal)", "sn", "221" ], [ "Serbia (Србија)", "rs", "381" ], [ "Seychelles", "sc", "248" ], [ "Sierra Leone", "sl", "232" ], [ "Singapore", "sg", "65" ], [ "Sint Maarten", "sx", "1", 21, [ "721" ] ], [ "Slovakia (Slovensko)", "sk", "421" ], [ "Slovenia (Slovenija)", "si", "386" ], [ "Solomon Islands", "sb", "677" ], [ "Somalia (Soomaaliya)", "so", "252" ], [ "South Africa", "za", "27" ], [ "South Korea (대한민국)", "kr", "82" ], [ "South Sudan (‫جنوب السودان‬‎)", "ss", "211" ], [ "Spain (España)", "es", "34" ], [ "Sri Lanka (ශ්‍රී ලංකාව)", "lk", "94" ], [ "Sudan (‫السودان‬‎)", "sd", "249" ], [ "Suriname", "sr", "597" ], [ "Svalbard and Jan Mayen", "sj", "47", 1, [ "79" ] ], [ "Swaziland", "sz", "268" ], [ "Sweden (Sverige)", "se", "46" ], [ "Switzerland (Schweiz)", "ch", "41" ], [ "Syria (‫سوريا‬‎)", "sy", "963" ], [ "Taiwan (台灣)", "tw", "886" ], [ "Tajikistan", "tj", "992" ], [ "Tanzania", "tz", "255" ], [ "Thailand (ไทย)", "th", "66" ], [ "Timor-Leste", "tl", "670" ], [ "Togo", "tg", "228" ], [ "Tokelau", "tk", "690" ], [ "Tonga", "to", "676" ], [ "Trinidad and Tobago", "tt", "1", 22, [ "868" ] ], [ "Tunisia (‫تونس‬‎)", "tn", "216" ], [ "Turkey (Türkiye)", "tr", "90" ], [ "Turkmenistan", "tm", "993" ], [ "Turks and Caicos Islands", "tc", "1", 23, [ "649" ] ], [ "Tuvalu", "tv", "688" ], [ "U.S. Virgin Islands", "vi", "1", 24, [ "340" ] ], [ "Uganda", "ug", "256" ], [ "Ukraine (Україна)", "ua", "380" ], [ "United Arab Emirates (‫الإمارات العربية المتحدة‬‎)", "ae", "971" ], [ "United Kingdom", "gb", "44", 0 ], [ "United States", "us", "1", 0 ], [ "Uruguay", "uy", "598" ], [ "Uzbekistan (Oʻzbekiston)", "uz", "998" ], [ "Vanuatu", "vu", "678" ], [ "Vatican City (Città del Vaticano)", "va", "39", 1, [ "06698" ] ], [ "Venezuela", "ve", "58" ], [ "Vietnam (Việt Nam)", "vn", "84" ], [ "Wallis and Futuna (Wallis-et-Futuna)", "wf", "681" ], [ "Western Sahara (‫الصحراء الغربية‬‎)", "eh", "212", 1, [ "5288", "5289" ] ], [ "Yemen (‫اليمن‬‎)", "ye", "967" ], [ "Zambia", "zm", "260" ], [ "Zimbabwe", "zw", "263" ], [ "Åland Islands", "ax", "358", 1, [ "18" ] ] ];
        for (var i = 0; i < allCountries.length; i++) {
            var c = allCountries[i];
            if (ph == '+'+c[2]) {
                return c[1].toUpperCase();
            }       
        }
    }

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
    function getCountryByPh(ph) {
        var allCountries = [ [ "Afghanistan (‫افغانستان‬‎)", "af", "93" ], [ "Albania (Shqipëri)", "al", "355" ], [ "Algeria (‫الجزائر‬‎)", "dz", "213" ], [ "American Samoa", "as", "1", 5, [ "684" ] ], [ "Andorra", "ad", "376" ], [ "Angola", "ao", "244" ], [ "Anguilla", "ai", "1", 6, [ "264" ] ], [ "Antigua and Barbuda", "ag", "1", 7, [ "268" ] ], [ "Argentina", "ar", "54" ], [ "Armenia (Հայաստան)", "am", "374" ], [ "Aruba", "aw", "297" ], [ "Australia", "au", "61", 0 ], [ "Austria (Österreich)", "at", "43" ], [ "Azerbaijan (Azərbaycan)", "az", "994" ], [ "Bahamas", "bs", "1", 8, [ "242" ] ], [ "Bahrain (‫البحرين‬‎)", "bh", "973" ], [ "Bangladesh (বাংলাদেশ)", "bd", "880" ], [ "Barbados", "bb", "1", 9, [ "246" ] ], [ "Belarus (Беларусь)", "by", "375" ], [ "Belgium (België)", "be", "32" ], [ "Belize", "bz", "501" ], [ "Benin (Bénin)", "bj", "229" ], [ "Bermuda", "bm", "1", 10, [ "441" ] ], [ "Bhutan (འབྲུག)", "bt", "975" ], [ "Bolivia", "bo", "591" ], [ "Bosnia and Herzegovina (Босна и Херцеговина)", "ba", "387" ], [ "Botswana", "bw", "267" ], [ "Brazil (Brasil)", "br", "55" ], [ "British Indian Ocean Territory", "io", "246" ], [ "British Virgin Islands", "vg", "1", 11, [ "284" ] ], [ "Brunei", "bn", "673" ], [ "Bulgaria (България)", "bg", "359" ], [ "Burkina Faso", "bf", "226" ], [ "Burundi (Uburundi)", "bi", "257" ], [ "Cambodia (កម្ពុជា)", "kh", "855" ], [ "Cameroon (Cameroun)", "cm", "237" ], [ "Canada", "ca", "1", 1, [ "204", "226", "236", "249", "250", "289", "306", "343", "365", "387", "403", "416", "418", "431", "437", "438", "450", "506", "514", "519", "548", "579", "581", "587", "604", "613", "639", "647", "672", "705", "709", "742", "778", "780", "782", "807", "819", "825", "867", "873", "902", "905" ] ], [ "Cape Verde (Kabu Verdi)", "cv", "238" ], [ "Caribbean Netherlands", "bq", "599", 1, [ "3", "4", "7" ] ], [ "Cayman Islands", "ky", "1", 12, [ "345" ] ], [ "Central African Republic (République centrafricaine)", "cf", "236" ], [ "Chad (Tchad)", "td", "235" ], [ "Chile", "cl", "56" ], [ "China (中国)", "cn", "86" ], [ "Christmas Island", "cx", "61", 2 ], [ "Cocos (Keeling) Islands", "cc", "61", 1 ], [ "Colombia", "co", "57" ], [ "Comoros (‫جزر القمر‬‎)", "km", "269" ], [ "Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)", "cd", "243" ], [ "Congo (Republic) (Congo-Brazzaville)", "cg", "242" ], [ "Cook Islands", "ck", "682" ], [ "Costa Rica", "cr", "506" ], [ "Côte d’Ivoire", "ci", "225" ], [ "Croatia (Hrvatska)", "hr", "385" ], [ "Cuba", "cu", "53" ], [ "Curaçao", "cw", "599", 0 ], [ "Cyprus (Κύπρος)", "cy", "357" ], [ "Czech Republic (Česká republika)", "cz", "420" ], [ "Denmark (Danmark)", "dk", "45" ], [ "Djibouti", "dj", "253" ], [ "Dominica", "dm", "1", 13, [ "767" ] ], [ "Dominican Republic (República Dominicana)", "do", "1", 2, [ "809", "829", "849" ] ], [ "Ecuador", "ec", "593" ], [ "Egypt (‫مصر‬‎)", "eg", "20" ], [ "El Salvador", "sv", "503" ], [ "Equatorial Guinea (Guinea Ecuatorial)", "gq", "240" ], [ "Eritrea", "er", "291" ], [ "Estonia (Eesti)", "ee", "372" ], [ "Ethiopia", "et", "251" ], [ "Falkland Islands (Islas Malvinas)", "fk", "500" ], [ "Faroe Islands (Føroyar)", "fo", "298" ], [ "Fiji", "fj", "679" ], [ "Finland (Suomi)", "fi", "358", 0 ], [ "France", "fr", "33" ], [ "French Guiana (Guyane française)", "gf", "594" ], [ "French Polynesia (Polynésie française)", "pf", "689" ], [ "Gabon", "ga", "241" ], [ "Gambia", "gm", "220" ], [ "Georgia (საქართველო)", "ge", "995" ], [ "Germany (Deutschland)", "de", "49" ], [ "Ghana (Gaana)", "gh", "233" ], [ "Gibraltar", "gi", "350" ], [ "Greece (Ελλάδα)", "gr", "30" ], [ "Greenland (Kalaallit Nunaat)", "gl", "299" ], [ "Grenada", "gd", "1", 14, [ "473" ] ], [ "Guadeloupe", "gp", "590", 0 ], [ "Guam", "gu", "1", 15, [ "671" ] ], [ "Guatemala", "gt", "502" ], [ "Guernsey", "gg", "44", 1, [ "1481", "7781", "7839", "7911" ] ], [ "Guinea (Guinée)", "gn", "224" ], [ "Guinea-Bissau (Guiné Bissau)", "gw", "245" ], [ "Guyana", "gy", "592" ], [ "Haiti", "ht", "509" ], [ "Honduras", "hn", "504" ], [ "Hong Kong (香港)", "hk", "852" ], [ "Hungary (Magyarország)", "hu", "36" ], [ "Iceland (Ísland)", "is", "354" ], [ "India (भारत)", "in", "91" ], [ "Indonesia", "id", "62" ], [ "Iran (‫ایران‬‎)", "ir", "98" ], [ "Iraq (‫العراق‬‎)", "iq", "964" ], [ "Ireland", "ie", "353" ], [ "Isle of Man", "im", "44", 2, [ "1624", "74576", "7524", "7924", "7624" ] ], [ "Israel (‫ישראל‬‎)", "il", "972" ], [ "Italy (Italia)", "it", "39", 0 ], [ "Jamaica", "jm", "1", 4, [ "876", "658" ] ], [ "Japan (日本)", "jp", "81" ], [ "Jersey", "je", "44", 3, [ "1534", "7509", "7700", "7797", "7829", "7937" ] ], [ "Jordan (‫الأردن‬‎)", "jo", "962" ], [ "Kazakhstan (Казахстан)", "kz", "7", 1, [ "33", "7" ] ], [ "Kenya", "ke", "254" ], [ "Kiribati", "ki", "686" ], [ "Kosovo", "xk", "383" ], [ "Kuwait (‫الكويت‬‎)", "kw", "965" ], [ "Kyrgyzstan (Кыргызстан)", "kg", "996" ], [ "Laos (ລາວ)", "la", "856" ], [ "Latvia (Latvija)", "lv", "371" ], [ "Lebanon (‫لبنان‬‎)", "lb", "961" ], [ "Lesotho", "ls", "266" ], [ "Liberia", "lr", "231" ], [ "Libya (‫ليبيا‬‎)", "ly", "218" ], [ "Liechtenstein", "li", "423" ], [ "Lithuania (Lietuva)", "lt", "370" ], [ "Luxembourg", "lu", "352" ], [ "Macau (澳門)", "mo", "853" ], [ "Macedonia (FYROM) (Македонија)", "mk", "389" ], [ "Madagascar (Madagasikara)", "mg", "261" ], [ "Malawi", "mw", "265" ], [ "Malaysia", "my", "60" ], [ "Maldives", "mv", "960" ], [ "Mali", "ml", "223" ], [ "Malta", "mt", "356" ], [ "Marshall Islands", "mh", "692" ], [ "Martinique", "mq", "596" ], [ "Mauritania (‫موريتانيا‬‎)", "mr", "222" ], [ "Mauritius (Moris)", "mu", "230" ], [ "Mayotte", "yt", "262", 1, [ "269", "639" ] ], [ "Mexico (México)", "mx", "52" ], [ "Micronesia", "fm", "691" ], [ "Moldova (Republica Moldova)", "md", "373" ], [ "Monaco", "mc", "377" ], [ "Mongolia (Монгол)", "mn", "976" ], [ "Montenegro (Crna Gora)", "me", "382" ], [ "Montserrat", "ms", "1", 16, [ "664" ] ], [ "Morocco (‫المغرب‬‎)", "ma", "212", 0 ], [ "Mozambique (Moçambique)", "mz", "258" ], [ "Myanmar (Burma) (မြန်မာ)", "mm", "95" ], [ "Namibia (Namibië)", "na", "264" ], [ "Nauru", "nr", "674" ], [ "Nepal (नेपाल)", "np", "977" ], [ "Netherlands (Nederland)", "nl", "31" ], [ "New Caledonia (Nouvelle-Calédonie)", "nc", "687" ], [ "New Zealand", "nz", "64" ], [ "Nicaragua", "ni", "505" ], [ "Niger (Nijar)", "ne", "227" ], [ "Nigeria", "ng", "234" ], [ "Niue", "nu", "683" ], [ "Norfolk Island", "nf", "672" ], [ "North Korea (조선 민주주의 인민 공화국)", "kp", "850" ], [ "Northern Mariana Islands", "mp", "1", 17, [ "670" ] ], [ "Norway (Norge)", "no", "47", 0 ], [ "Oman (‫عُمان‬‎)", "om", "968" ], [ "Pakistan (‫پاکستان‬‎)", "pk", "92" ], [ "Palau", "pw", "680" ], [ "Palestine (‫فلسطين‬‎)", "ps", "970" ], [ "Panama (Panamá)", "pa", "507" ], [ "Papua New Guinea", "pg", "675" ], [ "Paraguay", "py", "595" ], [ "Peru (Perú)", "pe", "51" ], [ "Philippines", "ph", "63" ], [ "Poland (Polska)", "pl", "48" ], [ "Portugal", "pt", "351" ], [ "Puerto Rico", "pr", "1", 3, [ "787", "939" ] ], [ "Qatar (‫قطر‬‎)", "qa", "974" ], [ "Réunion (La Réunion)", "re", "262", 0 ], [ "Romania (România)", "ro", "40" ], [ "Russia (Россия)", "ru", "7", 0 ], [ "Rwanda", "rw", "250" ], [ "Saint Barthélemy", "bl", "590", 1 ], [ "Saint Helena", "sh", "290" ], [ "Saint Kitts and Nevis", "kn", "1", 18, [ "869" ] ], [ "Saint Lucia", "lc", "1", 19, [ "758" ] ], [ "Saint Martin (Saint-Martin (partie française))", "mf", "590", 2 ], [ "Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)", "pm", "508" ], [ "Saint Vincent and the Grenadines", "vc", "1", 20, [ "784" ] ], [ "Samoa", "ws", "685" ], [ "San Marino", "sm", "378" ], [ "São Tomé and Príncipe (São Tomé e Príncipe)", "st", "239" ], [ "Saudi Arabia (‫المملكة العربية السعودية‬‎)", "sa", "966" ], [ "Senegal (Sénégal)", "sn", "221" ], [ "Serbia (Србија)", "rs", "381" ], [ "Seychelles", "sc", "248" ], [ "Sierra Leone", "sl", "232" ], [ "Singapore", "sg", "65" ], [ "Sint Maarten", "sx", "1", 21, [ "721" ] ], [ "Slovakia (Slovensko)", "sk", "421" ], [ "Slovenia (Slovenija)", "si", "386" ], [ "Solomon Islands", "sb", "677" ], [ "Somalia (Soomaaliya)", "so", "252" ], [ "South Africa", "za", "27" ], [ "South Korea (대한민국)", "kr", "82" ], [ "South Sudan (‫جنوب السودان‬‎)", "ss", "211" ], [ "Spain (España)", "es", "34" ], [ "Sri Lanka (ශ්‍රී ලංකාව)", "lk", "94" ], [ "Sudan (‫السودان‬‎)", "sd", "249" ], [ "Suriname", "sr", "597" ], [ "Svalbard and Jan Mayen", "sj", "47", 1, [ "79" ] ], [ "Swaziland", "sz", "268" ], [ "Sweden (Sverige)", "se", "46" ], [ "Switzerland (Schweiz)", "ch", "41" ], [ "Syria (‫سوريا‬‎)", "sy", "963" ], [ "Taiwan (台灣)", "tw", "886" ], [ "Tajikistan", "tj", "992" ], [ "Tanzania", "tz", "255" ], [ "Thailand (ไทย)", "th", "66" ], [ "Timor-Leste", "tl", "670" ], [ "Togo", "tg", "228" ], [ "Tokelau", "tk", "690" ], [ "Tonga", "to", "676" ], [ "Trinidad and Tobago", "tt", "1", 22, [ "868" ] ], [ "Tunisia (‫تونس‬‎)", "tn", "216" ], [ "Turkey (Türkiye)", "tr", "90" ], [ "Turkmenistan", "tm", "993" ], [ "Turks and Caicos Islands", "tc", "1", 23, [ "649" ] ], [ "Tuvalu", "tv", "688" ], [ "U.S. Virgin Islands", "vi", "1", 24, [ "340" ] ], [ "Uganda", "ug", "256" ], [ "Ukraine (Україна)", "ua", "380" ], [ "United Arab Emirates (‫الإمارات العربية المتحدة‬‎)", "ae", "971" ], [ "United Kingdom", "gb", "44", 0 ], [ "United States", "us", "1", 0 ], [ "Uruguay", "uy", "598" ], [ "Uzbekistan (Oʻzbekiston)", "uz", "998" ], [ "Vanuatu", "vu", "678" ], [ "Vatican City (Città del Vaticano)", "va", "39", 1, [ "06698" ] ], [ "Venezuela", "ve", "58" ], [ "Vietnam (Việt Nam)", "vn", "84" ], [ "Wallis and Futuna (Wallis-et-Futuna)", "wf", "681" ], [ "Western Sahara (‫الصحراء الغربية‬‎)", "eh", "212", 1, [ "5288", "5289" ] ], [ "Yemen (‫اليمن‬‎)", "ye", "967" ], [ "Zambia", "zm", "260" ], [ "Zimbabwe", "zw", "263" ], [ "Åland Islands", "ax", "358", 1, [ "18" ] ] ];
        for (var i = 0; i < allCountries.length; i++) {
            var c = allCountries[i];
            if (ph == '+'+c[2]) {
                return c[1].toUpperCase();
            }       
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
