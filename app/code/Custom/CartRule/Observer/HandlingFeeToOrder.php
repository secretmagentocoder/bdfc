<?php
namespace Custom\CartRule\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;

class HandlingFeeToOrder implements ObserverInterface
{
    private $objectCopyService;

    public function __construct(
      \Magento\Framework\DataObject\Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
      $order = $observer->getEvent()->getData('order');
      $quote = $observer->getEvent()->getData('quote');
      $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
      return $this;
    }
}