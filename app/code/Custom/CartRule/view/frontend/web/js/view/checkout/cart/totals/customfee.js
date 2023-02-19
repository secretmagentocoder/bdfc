define(
    [
        'Custom_CartRule/js/view/checkout/summary/customfee'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            /**
            * @override
            */
            isDisplayed: function () {
                return true;
            }
        });
    }
);