<?php
/**

@Author paygcc.com contact info@paygcc.com

 */
namespace PL\Paygcc\Controller\Benefit;


class Redirect extends \PL\Paygcc\Controller\Benefit
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
        $paymentUrl = $this->benefit->getPayGCCPaymentUrl($order);
        $resultRedirect = $this->resultRedirectFactory->setUrl($paymentUrl);
        return $resultRedirect;
    }

}
