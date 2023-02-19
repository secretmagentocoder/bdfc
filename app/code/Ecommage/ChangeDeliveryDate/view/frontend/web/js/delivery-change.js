define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data',
    'mage/calendar'

], function ($, alert, customerData) {
    'use strict';

    var showMessage = function (message, type) {
        alert({
            title: type,
            content: message
        });
    };

    return function (config, element) {
        $(element).find('#new_date').calendar({
            showOn: "both",
            showsTime: false,
            dateFormat: "mm/dd/yy",
            minDate: 0,
            controlType : 'select',
            timeFormat: "HH:mm",
            yearRange: '+0:+100'
        });

        $(element).on('click', 'button[type="submit"]', function (event) {
            event.preventDefault();
            let self = this,
                ignore = null,
                validateForm = false,
                ajaxUrl = config.ajaxUrl,
                receiptNo = $(element).find('input[name="receipt_no"]'),
                type = $('body').find('input[name="type"]'),
                dataForm = $(this).closest('form'),
                data = dataForm.serialize();

            dataForm.mage('validation', {});
            dataForm.mage('validation', {
                ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
            }).find('input:text').attr('autocomplete', 'off');
            validateForm = dataForm.validation('isValid'); //validates form and returns boolean
            if (validateForm) {
                $(self).prop('disabled', true);
                $.ajax({
                    url: ajaxUrl,
                    data: data,
                    type: "POST",
                    dataType: 'json',
                    showLoader: true,
                }).done(function (resp) {
                    $(self).prop('disabled', false);
                    dataForm.trigger('contentUpdated');
                    if (!resp.display) {
                         showMessage(resp.message, resp.status);
                    }
                });
            }
        });
    };
});
