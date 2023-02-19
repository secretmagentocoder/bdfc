<?php
/**

@Author paygcc.com contact info@paygcc.com

 */
namespace PL\Paygcc\Controller\ApiCheckout;


class Redirect extends \PL\Paygcc\Controller\ApiCheckout
{

    public function execute()
    {
        $incrementId = $this->checkoutSession->getLastRealOrderId();
        if (!isset($incrementId)) {
            $this->messageManager->addError("Invalid Data");
            $this->_redirect('checkout/cart');
            return;
        }
        $order = $this->_getOrder();
        $paymentUrl = $this->apiCheckout->getPayGCCPaymentUrl($order);
        $resultRedirect = $this->resultRedirectFactory->setUrl($paymentUrl);
        return $resultRedirect;
    }

}
