var config = {
    paths: {
        'slick': 'Magento_Theme/js/slick.min',
        'bootstrap': 'Magento_Theme/js/bootstrap.min',
        'mCustomScrollbar': 'Magento_Theme/js/mCustomScrollbar.concat.min',
        'magnific': 'Magento_Theme/js/jquery.magnific-popup.min',
        'tooltipster': 'Magento_Theme/js/tooltipster.bundle.min'
    },
    shim: {
        'slick': { 
            deps: ['jquery']
        },
        'bootstrap': { 
            deps: ['jquery']
        },
        'mCustomScrollbar': { 
            deps: ['jquery']
        },
        'magnific': { 
            deps: ['jquery']
        },
        'tooltipster': { 
            deps: ['jquery']
        }
    },
    "map": {
        "*": {
            'Magento_Checkout/template/minicart/content.html': 'Magento_Checkout/template/minicart/content.html',
            'Magento_Checkout/template/minicart/item/default.html': 'Magento_Checkout/template/minicart/item/default.html'
        }
    }
};
