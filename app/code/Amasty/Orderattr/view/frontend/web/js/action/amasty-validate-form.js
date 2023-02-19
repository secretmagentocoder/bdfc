define([
    'uiRegistry'
], function (registry) {
    'use strict';

    return function (attributesTypes) {
        var amastyCheckoutProvider = registry.get('amastyCheckoutProvider'),
            focused = false,
            result = {};

        for (var key in attributesTypes) {
            if (attributesTypes.hasOwnProperty(key)) {
                result = _.extend(result, amastyCheckoutProvider.get(attributesTypes[key]));
                amastyCheckoutProvider.set('params.invalid', false);

                var customScope = attributesTypes[key];
                if (customScope.indexOf('.') !== -1) {
                    customScope = customScope.substr(customScope.indexOf('.') + 1);
                }
                amastyCheckoutProvider.trigger(customScope + '.data.validate');

                if (amastyCheckoutProvider.get('params.invalid') && !focused) {
                    var container = registry.filter("index = " + attributesTypes[key] + 'Container');
                    if (container.length) {
                        container[0].focusInvalidField();
                    }
                    focused = true;
                    amastyCheckoutProvider.set('params.invalid', false);
                }
            }
        }

        if (focused) {
            amastyCheckoutProvider.set('params.invalid', true);
        }

        if (amastyCheckoutProvider.get('params.invalid')) {
            return false;
        } else {
            return result;
        }
    }
});
