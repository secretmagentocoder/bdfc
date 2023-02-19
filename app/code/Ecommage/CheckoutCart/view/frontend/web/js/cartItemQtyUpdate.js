define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/alert',
    'mage/url'
], function ($, getTotalsAction, customerData,modalConfirm,urlBuilder) {

    $(document).ready(function(){
        $(document).on('click', '.increaseQty, .decreaseQty', function(){
            $('.amsearch-close').trigger('click');
            var $this = $(this);
            var ctrl = ($(this).attr('id').replace('-upt','')).replace('-dec','');
            var currentQty = $("#cart-"+ctrl+"-qty").val();
            if($this.hasClass('increaseQty')){
                var newAdd = parseInt(currentQty)+parseInt(1);
                $("#cart-"+ctrl+"-qty").val(newAdd);
            }else{
                if(currentQty>1){
                    var newAdd = parseInt(currentQty)-parseInt(1);
                    $("#cart-"+ctrl+"-qty").val(newAdd);
                }
            }
            $('.control.qty .input-text.qty').trigger('change');

        });
        $(document).on('change paste', '.control.qty .input-text.qty', function(){
            var form = $('form#form-validate');
            var qty = $('[data-role=cart-item-qty]').val();
            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                showLoader: true,
                success: function (res) {
                    $('.amsearch-close').trigger('click');
                    var parsedResponse = $.parseHTML(res);
                    var result = $(parsedResponse).find("#form-validate");
                    var sections = ['cart'];
                    if(result.length === 0){
                        var url = urlBuilder.build(window.checkout.shoppingCartUrl);
                        window.location.href = url;
                    }
                    $("#form-validate").replaceWith(result);

                    /* This is for reloading the minicart */
                    customerData.reload(sections, true);

                    /* This is for reloading the totals summary  */
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);
                    var qtyUpdate = $('[data-role=cart-item-qty]').val();
                    if (qty > qtyUpdate){
                        if($('body').find('.modal-title').text() == ''){
                            modalConfirm({
                                title: $.mage.__('Attention'),
                                content: $.mage.__('The requested qty exceeds the maximum qty allowed in shopping cart'),
                            });
                        }
                    }
                    // location.reload();
                },
                error: function (xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                }
            });
        });

    });
});
