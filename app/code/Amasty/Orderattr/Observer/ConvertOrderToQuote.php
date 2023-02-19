<?php

namespace Amasty\Orderattr\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * event sales_convert_order_to_quote
 * name ConvertOrderAttributesToQuoteAttributes
 */
class ConvertOrderToQuote implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Amasty\Orderattr\Model\Entity\Adapter\Order\Adapter
     */
    private $orderAdapter;

    public function __construct(
        \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory,
        \Amasty\Orderattr\Model\Entity\Adapter\Order\Adapter $orderAdapter
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->orderAdapter = $orderAdapter;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Quote\Model\Quote $quote
         * @var \Magento\Sales\Model\Order $order
         */
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $orderExtensionAttributes = $order->getExtensionAttributes();
        if (!$orderExtensionAttributes || !$orderExtensionAttributes->getAmastyOrderAttributes()) {
            $this->orderAdapter->addExtensionAttributesToOrder($order);
            $orderExtensionAttributes = $order->getExtensionAttributes();
        }
        if ($orderExtensionAttributes->getAmastyOrderAttributes()) {
            $customAttributes = $orderExtensionAttributes->getAmastyOrderAttributes();
            $quoteExtensionAttributes = $quote->getExtensionAttributes();
            if (empty($quoteExtensionAttributes)) {
                $quoteExtensionAttributes = $this->cartExtensionFactory->create();
            }
            $quoteExtensionAttributes->setAmastyOrderAttributes($customAttributes);
            $quote->setExtensionAttributes($quoteExtensionAttributes);
        }
    }
}
