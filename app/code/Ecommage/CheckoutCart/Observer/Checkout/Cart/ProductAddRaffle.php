<?php

namespace Ecommage\CheckoutCart\Observer\Checkout\Cart;

use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Class ProductAddRaffle
 */
class ProductAddRaffle implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $optionValueCollectionFactory;

    /**
     * ProductAddRaffle constructor.
     *
     * @param CollectionFactory $optionValueCollectionFactory
     */
    public function __construct(
        CollectionFactory $optionValueCollectionFactory
    ) {
        $this->optionValueCollectionFactory = $optionValueCollectionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event   = $observer->getEvent();
        $product = $event->getData('product');
        /** @var Item $quoteItem */
        $quoteItem = $event->getData('quote_item');
        $isRaffle  = (int)$product->getData('is_check_raffle');
        if ($isRaffle) {
            $option    = $quoteItem->getOptionByCode('option_ids');
            $optionIds = (array)$option->getValue();
            if (strpos($option->getValue(), ',') !== false) {
                $optionIds = explode(',', $option->getValue());
            }

            foreach ($optionIds as $optionId) {
                $optionValue = $quoteItem->getOptionByCode('option_' . $optionId);
                if (!$optionValue) {
                    continue;
                }

                $optionTypeIds = (array)$optionValue->getValue();
                if (strpos($optionValue->getValue(), ',') !== false) {
                    $optionTypeIds = explode(',', $optionValue->getValue());
                }

                /** @var Collection $collection */
                $collection = $this->optionValueCollectionFactory->create();
                $collection->addFieldToFilter('option_type_id', ['in' => $optionTypeIds]);
                $collection->addFieldToFilter('option_id', $optionId);
                foreach ($collection as $optionType) {
                    $qty = $optionType->getQty() - $quoteItem->getQty();
                    $optionType->setQty($qty)->save();
                }
            }
        }
    }
}
