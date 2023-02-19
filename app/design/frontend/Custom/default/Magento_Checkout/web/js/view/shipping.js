/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
   'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'mage/url',
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
    'Magento_Checkout/js/model/shipping-rate-service',
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    urlBuilder,
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
    $t,
) {
    'use strict';

    var countryData = customerData.get('directory-data');
    let dob = customerData.get('dob');

    var popUp = null;
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
            template: 'Magento_Checkout/shipping',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item',
            imports: {
                countryOptions: '${ $.parentName }.shippingAddress.shipping-address-fieldset.country_id:indexedOptions'
            }
        },
        visible: ko.observable(true),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),

        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

            this._super();

            // if (!quote.isVirtual()) {
                stepNavigator.registerStep(
                    'shipping',
                    '',
                    $t('Shipping'),
                    this.visible, _.bind(this.navigate, this),
                    this.sortOrder
                );
            // }
            checkoutDataResolver.resolveShippingAddress();

            hasNewAddress = addressList.some(function (address) {
                return address.getType() == 'new-customer-address'; //eslint-disable-line eqeqeq
            });

            this.isNewAddressAdded(hasNewAddress);

            this.isFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });

            quote.shippingMethod.subscribe(function () {
                self.errorValidationMessage(false);
            });

            $('body').on('click', '.button-continue-no-login', function () {
            var current_store = quote.getStoreCode();
            if (current_store == 'home_delivery' ) {
                if (quote.isVirtual()) {
                    $("button[data-role=opc-continue-step3]").trigger("click");
                } else {
                    $("button[data-role=opc-continue]").trigger("click");
                }
            }
            });



                $('body').on('click', 'ul#country-listbox li', function () {
                    if (!customer.isLoggedIn()) {
                        var countryCode = $(this).data('country-code');
                        $('input[name="country_code"]').val(countryCode.toUpperCase());
                        if (countryCode) {
                            self._renderOption(countryCode);
                        }
                    }
                });


              if (!customer.isLoggedIn()) {
                $('body').on('click', '#shipping-method-buttons-container button', function () {
                    var data = $('#co-shipping-form').serializeArray();
                    self.setAttributeToQuote(data);
                });
            }

            registry.async('checkoutProvider')(function (checkoutProvider) {
                var shippingAddressData = checkoutData.getShippingAddressFromData();

                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
                checkoutProvider.on('shippingAddress', function (shippingAddrsData) {
                    if (shippingAddrsData.street && !_.isEmpty(shippingAddrsData.street[0])) {
                        checkoutData.setShippingAddressFromData(shippingAddrsData);
                    }
                });
                shippingRatesValidator.initFields(fieldsetName);
            });

            return this;
        },

        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

                /**
         *
         * @param nationData
         * @private
         */
        _renderOption: function (nationData) {
            var self = this;
            var options = ``;
            var keyCustomOption = 'cus_';

            // var data = window.checkoutConfig.regionDefault;
            // _.each(data, function (key,val) {
            //     var text = '';
            //     if (nationData.toUpperCase() == key.country_id.toUpperCase()) {

            //         options += "<option " + text + " value=" + key.region_id + ">" + key.name + "</option>";
            //     }
            // });
            if (!options) {
            _.each(window.checkoutConfig.getOptionNationality, function (key,val) {
                    var text = '';
                        options += "<option " + text + " value="+key.country_id + ">" + key.name + "</option>";
                }
            );
            }
            $('#opption-select').html(options);
        },


        _renderOptionFirstLoadNotLogin: function () {
            var self = this;
            $.get("https://ipinfo.io", function () {
            }, "jsonp").always(function (resp) {
                var countryCode = (resp && resp.country) ? resp.country : '';

                var options = ``;
                var text = '';
                var keyCustomOption = 'cus_';
                // var data = window.checkoutConfig.regionDefault;
                // _.each(data, function (key, val) {
                //     if (countryCode.toUpperCase() == key.country_id.toUpperCase()) {
                //         options += "<option " + text + " value=" + key.region_id + ">" + key.name + "</option>";
                //     }
                // });
                if (!options) {
                    _.each(window.checkoutConfig.getOptionNationality, function (key, val) {
                            options += "<option " + text + " value=" + key.country_id + ">" + key.name + "</option>";
                        }
                    );
                }

                $('#opption-select').html(options);
            });
        },

        getDobFormat: function () {
            let date = window.checkoutConfig.dobFormat;
            if (date) {
                return date;
            } else if(typeof dob() === 'object') {
                return null;
            }
            return dob;
        },

        getCustomerAttribute: function(){
            let attr = window.checkoutConfig.customerData.custom_attributes;
            return attr;
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

        getCountryName: function (countryId) {
            countryId = countryId.toUpperCase();
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                        /** @inheritdoc */
                        click: this.onClosePopUp.bind(this)
                    }
                ];

                /** @inheritdoc */
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                /** @inheritdoc */
                this.popUpForm.options.opened = function () {
                    // Store temporary address for revert action in case when user click cancel action
                    self.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Revert address and close modal.
         */
        onClosePopUp: function () {
            checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
            this.getPopUp().closeModal();
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            this.isFormPopUpVisible(true);
        },

        /**
         * Save new shipping address
         */
        saveNewAddress: function () {
            var addressData,
                newShippingAddress;

            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();

            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');
                // if user clicked the checkbox, its value is true or false. Need to convert.
                addressData['save_in_address_book'] = this.saveInAddressBook ? 1 : 0;

                // New address must be selected as a shipping address
                newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                this.getPopUp().closeModal();
                this.isNewAddressAdded(true);
            }
        },

        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) {
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);

            return true;
        },

        /**
         * Set shipping information handler
         */
        setShippingInformation: function () {
            if (this.validateShippingInformation()) {
                quote.billingAddress(null);
                checkoutDataResolver.resolveBillingAddress();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                });
                setShippingInformationAction().done(
                    function () {
                        stepNavigator.next();
                    }
                );
            }
            if (!this.validateShippingInformation()) {
            quote.billingAddress(null);
            checkoutDataResolver.resolveBillingAddress();
            registry.async('checkoutProvider')(function (checkoutProvider) {
                var shippingAddressData = checkoutData.getShippingAddressFromData();

                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
            });
            setShippingInformationAction().done(
                function () {
                    stepNavigator.next();
                }
            );
        }
        },

        /**
         * @return {Boolean}
         */
        validateShippingInformation: function () {
            var shippingAddress,
                addressData,
                loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn(),
                field,
                option = _.isObject(this.countryOptions) && this.countryOptions[quote.shippingAddress().countryId],
                messageContainer = registry.get('checkout.errors').messageContainer;

            // if (!quote.shippingMethod()) {
            //     this.errorValidationMessage(
            //         $t('The shipping method is missing. Select the shipping method and try again.')
            //     );
            //
            //     return false;
            // }

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }

            if (this.isFormInline) {
                this.source.set('params.invalid', false);
                this.triggerShippingDataValidateEvent();

                if (!quote.shippingMethod()['method_code']) {
                    this.errorValidationMessage(
                        $t('The shipping method is missing. Select the shipping method and try again.')
                    );
                }

                if (emailValidationResult &&
                    this.source.get('params.invalid') ||
                    !quote.shippingMethod()['method_code'] ||
                    !quote.shippingMethod()['carrier_code']
                ) {
                    this.focusInvalid();

                    return false;
                }

                shippingAddress = quote.shippingAddress();
                addressData = addressConverter.formAddressDataToQuoteAddress(
                    this.source.get('shippingAddress')
                );

                //Copy form data to quote shipping address object
                for (field in addressData) {
                    if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    shippingAddress['save_in_address_book'] = 0;
                }
                selectShippingAddress(shippingAddress);
            } else if (customer.isLoggedIn() &&
                option &&
                option['is_region_required'] &&
                !quote.shippingAddress().region
            ) {
                messageContainer.addErrorMessage({
                    message: $t('Please specify a regionId in shipping address.')
                });

                return false;
            }

            if (!emailValidationResult) {
                $(loginFormSelector + ' input[name=username]').focus();

                return false;
            }

            return true;
        },

         setAttributeToQuote: function (data) {
            var url = urlBuilder.build('ecommage_checkout/ajax/setcustomattributetoquote');
            $.ajax({
                url: url,
                data: {
                    data: data
                },
                type: 'POST',
                cache: true,
                dataType: 'json',
                context: this
            });
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


        /**
         * Trigger Shipping data Validate Event.
         */
        triggerShippingDataValidateEvent: function () {
            this.source.trigger('shippingAddress.data.validate');

            if (this.source.get('shippingAddress.custom_attributes')) {
                this.source.trigger('shippingAddress.custom_attributes.data.validate');
            }
        },

        yearRestriction: function () {
            let yearLimit = window.checkoutConfig.year_limit;
            return yearLimit;
        }
    });
});
