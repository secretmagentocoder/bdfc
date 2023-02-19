<?php
namespace Custom\CartRule\Plugin\Quote;

use Closure;

class QuoteToOrderItem
{
   /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return \Magento\Sales\Model\Order\Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional); 
        $orderItem->setData('handling_charges', $item->getHandlingCharges());
        $orderItem->setData('base_handling_charges', $item->getBaseHandlingCharges());
        $orderItem->setData('handling_charges_tax', $item->getHandlingChargesTax());
        $orderItem->setData('base_handling_charges_tax', $item->getBaseHandlingChargesTax());
        $orderItem->setData('base_custom_duty_per_item', $item->getBaseCustomDutyPerItem());
        $orderItem->setData('custom_considered_qty', $item->getBaseCustomDutyPerItem());
        return $orderItem;
    }

}
