/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'underscore',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'Magento_Customer/js/model/customer',
    'mage/calendar',
    'uiRegistry',
    'prototype',
    'domReady!',
], function ($, ko, Component, _, selectShippingAddressAction, quote, formPopUpState, checkoutData, customerData, urlBuilder,customer, calender, registry) {
    'use strict';

    var countryData = customerData.get('directory-data');

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
        defaults: {
            template: 'Magento_Checkout/shipping-address/address-renderer/default'
        },

        /** @inheritdoc */
        initObservable: function () {
            var self = this;
            this._super();
            this.isSelected = ko.computed(function () {
                var isSelected = false,
                    shippingAddress = quote.shippingAddress();

                    var current_store = quote.getStoreCode();
                    if (current_store == 'home_delivery' ) {
                        var max = this.address().street.length;
                        var arr = window.checkoutConfig.streetData.length + 1;
                        if (max < arr) {
                            for (var i = max; i < arr; i++) {
                                this.address().street.push('');
                            }
                        }
                    }

                if (shippingAddress) {
                    isSelected = shippingAddress.getKey() == this.address().getKey(); //eslint-disable-line eqeqeq
                }
                return isSelected;
            }, this);

            // $('body').on('click', '.button-continue', function () {
            //     $("button[data-role=opc-continue]").trigger("click");
            // });
           $('body').on('click', '.button-continue', function () {
                    var current_store = quote.getStoreCode();
                    if (current_store == 'home_delivery' ) {

                        var street = $('#custom-shipping-new-address-form input[name="street[]"]').map(function(){return $(this).val();}).get();;

                        var validate  = self.validateForm(street);

                        if (validate == 1) {
                            $('.personal_information_response_step3').addClass('error').html('Please fill all required fields');
                            return false;
                        }

                        if (quote.isVirtual()) {
                            $("button[data-role=opc-continue-step3]").trigger("click");
                        } else {
                            $("button[data-role=opc-continue]").trigger("click");
                        }
                    }
                });


           $('body').on('click', 'ul#country-listbox li', function () {
                if (customer.isLoggedIn()) {
                    var countryCode = $(this).data('country-code');
                    $('input[name="country_code"]').val(countryCode.toUpperCase());
                    if (countryCode) {
                        self._renderOption(countryCode);
                    }
                }
            });

           // ajax request to delivery address
            $('body').on('click', '#shipping-method-buttons-container button', function () {

                var firstname = $('#custom-shipping-new-address-form input[name="firstname"]').val();
                var lastname = $('#custom-shipping-new-address-form input[name="lastname"]').val();
                var cust_dob = $('#custom-shipping-new-address-form input[name="customer_dob"]').val();
                var nationality = $('#custom-shipping-new-address-form select[name="nationality"]').val();
                var mobile_number = $('#custom-shipping-new-address-form input[name="mobile_number"]').val();
                var passport_no = $('#custom-shipping-new-address-form input[name="passport_no"]').val();
                var country_customer = $('#custom-shipping-new-address-form input[name="country_customer"]').val();

                var email = $('#custom-shipping-new-address-form input[name="email"]').val();

                var street = $('#custom-shipping-new-address-form input[name="street[]"]').map(function(){return $(this).val();}).get();;

                var save_address = $('body').find('input#save_address:checked').val();

                var obj = {
                    'firstname': firstname,
                    'lastname': lastname,
                    'customer_dob': cust_dob,
                    'nationality': nationality,
                    'mobile_number': mobile_number,
                    'passport_no': passport_no ? passport_no : '',
                    'country_customer': country_customer ? country_customer : '',
                    'email': email,

                    'street': street ? street : '',
                    'save_address': save_address ? 1 : 0,
                }
                      self.setAttributeToQuote(obj);

                    });
                    return this;
                },

        /**
         *
         * @returns {*}
         */
        getCustomerAttribute: function () {
            let data = window.checkoutConfig.customerData.custom_attributes;
            return data;
        },

        getStreetData: function () {
            let data = window.checkoutConfig.streetData;
            return data;
        },

        // check step 2 store
        checkVisibleStepFlight: function(){
            return quote.isVirtual();
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

        getCountryCodeCustomer: function(){
            let code = this.getCustomerAttribute().country_code ? this.getCustomerAttribute().country_code.value.toUpperCase() : '';
            return code;
        },

        getNationalityCustomer: function(){
            let code = this.getCustomerAttribute().national_id ? this.getCustomerAttribute().national_id.value.toUpperCase() : '';
            return code;
        },


        /**
         *
         * @returns {*}
         */
        getCustomerData: function () {
            let data = window.checkoutConfig.customerData;
            return data;
        },

        /**
         * @param {String} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            countryId = countryId.toUpperCase();
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        getNational: function () {
            var nationalityCustom = window.checkoutConfig.nationalityCustom;
            var regionCollection = window.checkoutConfig.regionDefault;
            var nationalId = this.getCustomerAttribute().national_id ? this.getCustomerAttribute().national_id.value : '';
            // check region id is custom or default
            if (nationalId) {
                var nationalName = '';
                _.each(nationalityCustom, function (val) {
                    if ('cus_' + val.value == nationalId) {
                        nationalName = val.label;
                        return;
                    }
                });
                _.each(regionCollection, function (val) {
                    if (val.region_id == nationalId) {
                        nationalName = val.default_name;
                        return;
                    }
                });
            }
            return nationalName;
        },

        getDobFormat: function () {
            let date = window.checkoutConfig.dobFormat;
            return date;
        },

        /**
         * Get customer attribute label
         *
         * @param {*} attribute
         * @returns {*}
         */
        getCustomAttributeLabel: function (attribute) {
            var label;

            if (typeof attribute === 'string') {
                return attribute;
            }

            if (attribute.label) {
                return attribute.label;
            }

            if (_.isArray(attribute.value)) {
                label = _.map(attribute.value, function (value) {
                    return this.getCustomAttributeOptionLabel(attribute['attribute_code'], value) || value;
                }, this).join(', ');
            } else {
                label = this.getCustomAttributeOptionLabel(attribute['attribute_code'], attribute.value);
            }

            return label || attribute.value;
        },

        /**
         * Get option label for given attribute code and option ID
         *
         * @param {String} attributeCode
         * @param {String} value
         * @returns {String|null}
         */
        getCustomAttributeOptionLabel: function (attributeCode, value) {
            var option,
                label,
                options = this.source.get('customAttributes') || {};

            if (options[attributeCode]) {
                option = _.findWhere(options[attributeCode], {
                    value: value
                });

                if (option) {
                    label = option.label;
                }
            }

            return label;
        },

        /** Set selected customer shipping address  */
        selectAddress: function () {
            selectShippingAddressAction(this.address());
            checkoutData.setSelectedShippingAddress(this.address().getKey());
        },

        /**
         * Edit address.
         */
        editAddress: function () {
            formPopUpState.isVisible(true);
            this.showPopup();

        },

        /**
         * Show popup.
         */
        showPopup: function () {
            $('[data-open-modal="opc-new-shipping-address"]').trigger('click');
        },

        /**
         * Show arrival form
         */
        arrival_form_visible: function () {
            // console.log(quote.getStoreCode());
            var current_store = quote.getStoreCode();
            if (current_store == 'arrival') {
                return true;
            }

            return false;
        },

        /**
         * Show departure form
         */
        departure_form_visible: function () {
            // console.log(quote.getStoreCode());
            var current_store = quote.getStoreCode();
            if (current_store == 'departure') {
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

        validateForm : function ($arr) {
            var i = '';
            $.each($arr , function (key,value) {
                if (value[key] == undefined){
                   i = 1;
                }
            })
            return i;
        },

        requiredFieldPersonal: function () {
            var firstname = $('#custom-shipping-new-address-form input[name="firstname"]').val();
            var lastname = $('#custom-shipping-new-address-form input[name="lastname"]').val();
            var cust_dob = $('#custom-shipping-new-address-form input[name="customer_dob"]').val();
            var nationality = $('#custom-shipping-new-address-form select[name="nationality"]').val();
            var mobile_number = $('#custom-shipping-new-address-form input[name="mobile_number"]').val();
            var passport_no = $('#custom-shipping-new-address-form input[name="passport_no"]').val() == undefined ? 1 : $('#custom-shipping-new-address-form input[name="passport_no"]').val();
            var country_customer = $('#custom-shipping-new-address-form input[name="country_customer"]').val() == undefined ? 1 : $('#custom-shipping-new-address-form input[name="country_customer"]').val();

            var email = $('#custom-shipping-new-address-form input[name="email"]').val();

            console.log(passport_no,country_customer);
            if (!firstname || !lastname || !cust_dob || !nationality || !mobile_number ||  !email || !passport_no || !country_customer) {
                return false;
            }
            return true;
        },

        /**
         * @returns void
         */
        setPersonalInformation: function () {
            $('.personal_information_response').html('');
            if (!this.requiredFieldPersonal()) {
                $('.personal_information_response').addClass('error').html('Please fill all required fields');
                return false;
            }

            // display only step flight
            $('.personal_information_response').html("").removeClass("error");
            if(quote.getStoreCode() == 'home_delivery'){
                $('.custom-checkout-step').find('.co-step-title').removeClass("active");
                $('.custom-checkout-step').find('.co-step-content').removeClass("active");
    
                $('.custom-checkout-step').find('.co-step-title').removeClass("prev_step");
                $('.shipping-address-personal').find('.co-step-title').addClass("prev_step");
    
                $('.shipping-address-delivery').find('.co-step-title').addClass("active");
                $('.shipping-address-delivery').find('.co-step-content').addClass("active");
            }

            else if(quote.getStoreCode() != 'home_delivery' && !this.checkVisibleStepFlight()){

                $('.custom-checkout-step').find('.co-step-title').removeClass("active");
                $('.custom-checkout-step').find('.co-step-content').removeClass("active");
    
                $('.custom-checkout-step').find('.co-step-title').removeClass("prev_step");
                $('.shipping-address-personal').find('.co-step-title').addClass("prev_step");
    
                $('.shipping-address-delivery').find('.co-step-title').addClass("active");
                $('.shipping-address-delivery').find('.co-step-content').addClass("active");
            }

            // display only step delivery
            else {
                $('.custom-checkout-step').find('.co-step-title').removeClass("active");
                $('.custom-checkout-step').find('.co-step-content').removeClass("active");

                $('.custom-checkout-step').find('.co-step-title').removeClass("prev_step");
                $('.shipping-address-personal').find('.co-step-title').addClass("prev_step");

                $('li#opc-delivery-checkout-form').find('.co-step-title').addClass('active');
                $('li#opc-delivery-checkout-form').find('.co-step-content').addClass('active');
            }

            // console.log(quote.getStoreCode());
            var current_store = quote.getStoreCode();
            if (current_store == 'home_delivery') {
                $('.checkout-shipping-method').addClass("active");
                $('.checkout-shipping-method').addClass("visible");
            }
            //      else {
            //     $('.checkout-shipping-method').addClass("active");
            //     $('.checkout-shipping-method').removeClass("visible");
            // }
            if (this.getIsVirtual() && quote.getStoreCode() == 'departure') {
                $('#shipping-method-buttons-container').find('button[data-role=opc-continue]').trigger('click');
            }

            if (this.getIsVirtual() && quote.getStoreCode() == 'arrival') {
                $('#shipping-method-buttons-container').find('button[data-role=opc-continue-step3]').trigger('click');
            }

            return false;
        },

         getIsVirtual: function () {
            return quote.isVirtual();
        },

        setAttributeToQuote: function (data, requestType) {
            var url = window.checkoutConfig.baseUrl + 'ecommage_checkout/ajax/setcustomattributetoquote';
            $.ajax({
                url: url,
                data: {
                    data: data,
                    request_type: requestType
                },
                type: 'POST',
                cache: true,
                dataType: 'json',
                context: this
            });
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

        /**
         * @returns void
         */
        getAddressUrl: function () {
            var customer_address_url = urlBuilder.build('customer/address/index');

            return customer_address_url;
        },

        /**
         * @returns void
         */
        getCustomerEmail: function () {
            var customer = customerData.get('customer');
            var customer_email = customer._latestValue.email;

            return customer_email;
        }
    });
});
