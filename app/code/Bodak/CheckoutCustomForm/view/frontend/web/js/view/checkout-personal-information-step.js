define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        customer
    ) {
        'use strict';
        /**
        * check-login - is the name of the component's .html template
        */
        return Component.extend({
            defaults: {
                template: 'Bodak_CheckoutCustomForm/order-personal-information'
            },

            isVisible: ko.observable(true),
            isLogedIn: customer.isLoggedIn(),
            stepCode: 'personal_information',
            stepTitle: 'Personal Information',

            /**
            *
            * @returns {*}
            */
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    this.stepCode,
                    //step alias
                    null,
                    this.stepTitle,
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                    * sort order value
                    * 'sort order value' < 10: step displays before shipping step;
                    * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                    * 'sort order value' > 20 : step displays after payment step
                    */
                    0
                );

                return this;
            },

            /**
            * The navigate() method is responsible for navigation between checkout step
            * during checkout. You can add custom logic, for example some conditions
            * for switching to your custom step
            */
            navigate: function () {

            },

            /**
            * @returns void
            */
            navigateToNextStep: function () {
                stepNavigator.next();
            }
        });
    }
);