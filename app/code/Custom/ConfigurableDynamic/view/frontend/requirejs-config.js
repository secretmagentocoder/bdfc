
var config = {
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'Custom_ConfigurableDynamic/js/model/skuswitch': true
            },
			'Magento_Swatches/js/swatch-renderer': {
                'Custom_ConfigurableDynamic/js/model/swatch-skuswitch': true
            }
        }
    }
};
