define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.changeEmailMobilePassword', {
        options: {
            changeEmailSelector: '[data-role=change-email]',
            changeMobileNumberSelector: '[data-role=change-mobile-number]',
            changePasswordSelector: '[data-role=change-password]',
            mainContainerSelector: '[data-container=change-email-mobile-password]',
            titleSelector: '[data-title=change-email-mobile-password]',
            emailContainerSelector: '[data-container=change-email]',
            mobileNumberContainerSelector: '[data-container=change-mobile-number]',
            newPasswordContainerSelector: '[data-container=new-password]',
            confirmPasswordContainerSelector: '[data-container=confirm-password]',
            currentPasswordSelector: '[data-input=current-password]',
            emailSelector: '[data-input=change-email]',
            mobileNumberSelector: '[data-input=change-mobile-number]',
            newPasswordSelector: '[data-input=new-password]',
            confirmPasswordSelector: '[data-input=confirm-password]'
        },

        /**
         * Create widget
         * @private
         */
        _create: function () {
            this.element.on('change', $.proxy(function () {
                this._checkChoice();
            }, this));

            this._checkChoice();
            this._bind();
        },

        /**
         * Event binding, will monitor change, keyup and paste events.
         * @private
         */
        _bind: function () {
            this._on($(this.options.emailSelector), {
                'change': this._updatePasswordFieldWithEmailValue,
                'keyup': this._updatePasswordFieldWithEmailValue,
                'paste': this._updatePasswordFieldWithEmailValue
            });
        },

        /**
         * Check choice
         * @private
         */
        _checkChoice: function () {
            if ($(this.options.changeEmailSelector).is(':checked') &&
                $(this.options.changePasswordSelector).is(':checked') &&
                $(this.options.changeMobileNumberSelector).is(':checked')) {
                this._showAll();
            } else if ($(this.options.changeEmailSelector).is(':checked') &&
                $(this.options.changePasswordSelector).is(':checked')) {
                this._showEmailPassword();
            } else if ($(this.options.changeEmailSelector).is(':checked') &&
                $(this.options.changeMobileNumberSelector).is(':checked')) {
                this._showEmailMobileNumber();
            } else if ($(this.options.changePasswordSelector).is(':checked') &&
                $(this.options.changeMobileNumberSelector).is(':checked')) {
                this._showMobileNumberPassword();
            } else if ($(this.options.changeEmailSelector).is(':checked')) {
                this._showEmail();
            } else if ($(this.options.changeMobileNumberSelector).is(':checked')) {
                this._showMobileNumber();
            } else if ($(this.options.changePasswordSelector).is(':checked')) {
                this._showPassword();
            }   else {
                this._hideAll();
            }
        },

        /**
         * Show email, mobile number and password input fields
         * @private
         */
        _showAll: function () {
            $(this.options.titleSelector).html(this.options.titleChangeEmailAndMobileNumberAndPassword);

            $(this.options.mainContainerSelector).show();
            $(this.options.emailContainerSelector).show();
            $(this.options.mobileNumberContainerSelector).show();
            $(this.options.newPasswordContainerSelector).show();
            $(this.options.confirmPasswordContainerSelector).show();

            $(this.options.currentPasswordSelector).attr('data-validate', '{required:true}').prop('disabled', false);
            $(this.options.mobileNumberSelector).attr('data-validate', '{required:true, \'validate-digits\':true,\'validate-mobile-number\':true}').prop('disabled', false);
            $(this.options.emailSelector).attr('data-validate', '{required:true}').prop('disabled', false);
            this._updatePasswordFieldWithEmailValue();
            $(this.options.confirmPasswordSelector).attr(
                'data-validate',
                '{required:true, equalTo:"' + this.options.newPasswordSelector + '"}'
            ).prop('disabled', false);
        },

        /**
         * Hide email, mobile number and password input fields
         * @private
         */
        _hideAll: function () {
            $(this.options.mainContainerSelector).hide();
            $(this.options.emailContainerSelector).hide();
            $(this.options.mobileNumberContainerSelector).hide();
            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();

            $(this.options.currentPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.mobileNumberSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show email and password input fields
         * @private
         */
        _showEmailPassword: function () {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeEmailAndPassword);

            $(this.options.mobileNumberContainerSelector).hide();

            $(this.options.mobileNumberSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show email and mobile number input fields
         * @private
         */
        _showEmailMobileNumber: function () {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeEmailAndMobileNumber);

            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();

            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show mobile number and password input fields
         * @private
         */
        _showMobileNumberPassword: function () {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeMobileNumberAndPassword);

            $(this.options.emailContainerSelector).hide();

            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show email input fields
         * @private
         */
        _showEmail: function () {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeEmail);

            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();
            $(this.options.mobileNumberContainerSelector).hide();

            $(this.options.mobileNumberSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show mobile number input fields
         * @private
         */
        _showMobileNumber: function () {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeMobileNumber);

            $(this.options.emailContainerSelector).hide();
            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();

            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show password input fields
         * @private
         */
        _showPassword: function () {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangePassword);

            $(this.options.emailContainerSelector).hide();
            $(this.options.mobileNumberContainerSelector).hide();

            $(this.options.mobileNumberSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Update password validation rules with email input field value
         * @private
         */
        _updatePasswordFieldWithEmailValue: function () {
            $(this.options.newPasswordSelector).attr(
                'data-validate',
                '{required:true, ' +
                '\'validate-customer-password\':true, ' +
                '\'password-not-equal-to-user-name\':\'' + $(this.options.emailSelector).val() + '\'}'
            ).prop('disabled', false);
        }
    });

    return $.mage.changeEmailMobilePassword;
});
