define([
    'jquery',
    'countryCode'
], function ($, countryCode) {
    'use strict';

    let mobileNumber = $('#mobile_number'),
        departurePickupAnotherMobile = $('#departure_mobile_number'),
        arrivalPickupAnotherMobile = $('#arrival_mobile_number'),
        countryCodeInput = $('input[name="country_code"]'),
        arrivalCountryCodeInput = $('input[name="arrival_country_code"]'),
        departureCountryCodeInput = $('input[name="departure_country_code"]');

    if($('.sparsh-mobile-number-login-option').length) {
        countryCode.changeLoginUser($('input[name="user_option"]'), 'login[username]');
    }

    // set flag
    countryCode.setCountryDropdown(mobileNumber, countryCodeInput);
    countryCode.setCountryDropdown(departurePickupAnotherMobile, departureCountryCodeInput);
    countryCode.setCountryDropdown(arrivalPickupAnotherMobile, arrivalCountryCodeInput);

    // validate
    countryCode.validateMobileNumber(mobileNumber, countryCodeInput);
    countryCode.validateMobileNumber(departurePickupAnotherMobile, departureCountryCodeInput);
    countryCode.validateMobileNumber(arrivalPickupAnotherMobile, arrivalCountryCodeInput);
});
