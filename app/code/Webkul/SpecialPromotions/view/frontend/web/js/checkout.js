require([
    'jquery',
    "uiRegistry",
    'Magento_Ui/js/modal/alert',
    'mage/url',
    'prototype',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/cart/totals-processor/default'
], function(jQuery, registry, alert,urlBuilder,prototype,getTotalsAction,quote,totalsDefaultProvider) {

    jQuery( document ).ready(function() {

        var existCondition = setInterval(function() {
            if (jQuery('.discount').length) { 
                clearInterval(existCondition);
                runMyFunction();
            }
        }, 100);
        // var customLink = urlBuilder.build('wkrules/update/cart');
        //     jQuery.ajax({
        //         url: customLink, 
        //         success: function(result) {
                    
        //             setTimeout(() => {
        //                 // var deferred = jQuery.Deferred();
        //                 // getTotalsAction([], deferred);
        //                 totalsDefaultProvider.estimateTotals(quote.shippingAddress());
        //             }, 2000);
                    
                   
        //         }
        //     });
     
        function runMyFunction(){
            
            var customLink = urlBuilder.build('wkrules/update/cart');
            setTimeout(() => {
                jQuery.ajax({
                    url: customLink, 
                    success: function(result) {
                        
                        
                            // var deferred = jQuery.Deferred();
                            // getTotalsAction([], deferred);
                            totalsDefaultProvider.estimateTotals(quote.shippingAddress());
                        
                        
                    
                    }
                });
            }, 2000);
        }
       
        
     });
    
    
     
});

