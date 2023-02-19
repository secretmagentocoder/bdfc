<?php
/**

@Author paygcc.com contact info@paygcc.com

 */
namespace PL\Paygcc\Controller\ApiCheckout;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Failed extends \PL\Paygcc\Controller\ApiCheckout
    implements CsrfAwareActionInterface, HttpPostActionInterface, HttpGetActionInterface
{

    public function execute()
    {
        $incrementId = $this->checkoutSession->getLastRealOrderId();
        if (!isset($incrementId)) {
            $this->messageManager->addError("Invalid Data");
            $this->_redirect('checkout/cart');
            return;
        }
        $payGCCOrderId = $this->apiCheckout->getPayGCCOrderId($incrementId);
        $response = $this->apiCheckout->getPayGCCOrderDetails($payGCCOrderId);
        if ($this->apiCheckout->getConfigData('debug')) {
            $this->plLogger->debug('Api Checkout Response:'.print_r($response,1));
        }
        $paymentInfo = $this->paygccHelper->getTransactionDetails($response);
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if((isset($paymentInfo['result']) && $paymentInfo['result'] == 'FAILURE')
            || (isset($paymentInfo['status']) && $paymentInfo['status'] == 'FAILURE')) {
            $this->apiCheckout->rejectTransaction($order, $paymentInfo);
            $this->messageManager->addError(__('Payment failed. Please try again'));
            $this->checkoutSession->restoreQuote();
        }

        $this->_redirect('checkout/cart');
        return;
    }


    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }


    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

}

