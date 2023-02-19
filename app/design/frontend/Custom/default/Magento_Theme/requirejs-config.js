var config = {
    paths: {
        'slick': 'Magento_Theme/js/slick.min',
        'bootstrap': 'Magento_Theme/js/bootstrap.min'
    },
    shim: {
        'slick': { 
            deps: ['jquery']
        },
        'bootstrap': { 
            deps: ['jquery']
        }
    },
    map: {
        '*': {
            customJS: 'Magento_Theme/js/custom',
            bootstrapBundle: 'Magento_Theme/js/bootstrap.bundle.min'
        }
    }
};
