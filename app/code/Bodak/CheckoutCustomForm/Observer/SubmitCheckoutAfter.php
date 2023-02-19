<?php

namespace Bodak\CheckoutCustomForm\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SubmitCheckoutAfter implements ObserverInterface
{
    public function __construct
    (
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
       $order = $observer->getData('order');
       $quote = $this->checkoutSession->getQuote();
       $order->setCustomfee($quote->getHandlingCharges() ?? 0 )->save();
    }
}