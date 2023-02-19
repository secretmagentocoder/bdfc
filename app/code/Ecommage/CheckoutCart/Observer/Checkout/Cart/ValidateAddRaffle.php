<?php

namespace Ecommage\CheckoutCart\Observer\Checkout\Cart;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class ProductAddRaffle
 */
class ValidateAddRaffle implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * ProductAddRaffle constructor.
     *
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event       = $observer->getEvent();
        $product     = $event->getData('product');
        $requestInfo = $event->getData('info');
        $isRaffle    = (int)$product->getData('is_check_raffle');
        if ($isRaffle) {
            $items = $this->getQuote()->getAllItems();
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($items as $item) {
                $productId = $item->getProductId();
                if ($productId == $product->getId()) {
                    $options = $requestInfo['options'] ?? [];
                    foreach ($options as $optionId => $values) {
                        $itemOption = $item->getOptionByCode('option_'. $optionId);
                        if ($itemOption) {
                            $valueIds = (array)$itemOption->getValue();
                            if (strpos($itemOption->getValue(), ',') !== false) {
                                $valueIds = explode(',', $itemOption->getValue());
                            }

                            $isAlready = array_intersect($valueIds, $values);
                            if (!empty($isAlready)) {
                                throw new LocalizedException(
                                    new Phrase('Products already in the cart.')
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}
