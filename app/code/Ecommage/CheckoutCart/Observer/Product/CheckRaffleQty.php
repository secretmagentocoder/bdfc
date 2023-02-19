<?php

namespace Ecommage\CheckoutCart\Observer\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory;

use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Class DeleteRaffle
 *
 * @package Ecommage\CheckoutCart\Observer\Checkout\Cart
 */
class CheckRaffleQty implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_registry = null;
    /**
     * @var Session
     */
    protected $_checkoutSession;
    /**
     * @var CollectionFactory
     */
    protected $optionValueCollectionFactory;

    /**
     * CheckRaffleQty constructor.
     *
     * @param Registry          $registry
     * @param Session           $checkoutSession
     * @param CollectionFactory $optionValueCollectionFactory
     */
    public function __construct(
        Registry $registry,
        Session $checkoutSession,
        CollectionFactory $optionValueCollectionFactory
    ) {
        $this->_registry                    = $registry;
        $this->_checkoutSession             = $checkoutSession;
        $this->optionValueCollectionFactory = $optionValueCollectionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $this->getProduct();
        if (!$product || $product->getData('is_check_raffle') != 1) {
            return $this;
        }

        $event = $observer->getEvent();
        /** @var DataObject $configObj */
        $configObj = $event->getData('configObj');
        $this->deleteTicketInQuote($configObj, $product);
        $this->deleteTicketOutOfStock($configObj, $product);
    }

    /**
     * @param DataObject $configObj
     * @param Product    $product
     *
     * @return DataObject
     */
    public function deleteTicketOutOfStock(DataObject $configObj, Product $product)
    {
        $config = $configObj->getConfig();
        if (!empty($config)) {
            foreach ($configObj->getConfig() as $optionId => $optionType) {
                $optionTypeIds = array_keys($optionType);
                /** @var Collection $collection */
                $collection = $this->optionValueCollectionFactory->create();
                $collection->addFieldToFilter('option_type_id', ['in' => $optionTypeIds]);
                $collection->addFieldToFilter('option_id', $optionId);
                $collection->addFieldToFilter('qty', ['lteq' => 0]);
                $collection->addFieldToFilter('manage_stock', 1);
                $oosIds = $collection->getAllIds();
                foreach ($oosIds as $id) {
                    $key = sprintf('config/%d/%d', $optionId, $id);
                    $configObj->unsetData($key); //remove custom option oos
                }
            }
        }

        return $configObj;
    }

    /**
     * @param DataObject $configObj
     * @param Product    $product
     *
     * @return DataObject
     */
    public function deleteTicketInQuote(DataObject $configObj, Product $product)
    {
        $items  = $this->getQuote()->getAllItems();
        $config = $configObj->getConfig();
        /** @var Item $item */
        foreach ($items as $item) {
            if ($item->getProductId() == $product->getId()) {
                $options = $item->getOptionByCode('option_ids');
                if (!$options) {
                    continue;
                }

                $optionIds = (array)$options->getValue();
                if (strpos($options->getValue(), ',') !== false) {
                    $optionIds = explode(',', $options->getValue());
                }

                foreach ($optionIds as $optionId) {
                    $itemOption = $item->getOptionByCode('option_' . $optionId);
                    if (!$itemOption) {
                        continue;
                    }

                    $valueIds = (array)$itemOption->getValue();
                    if (strpos($itemOption->getValue(), ',') !== false) {
                        $valueIds = explode(',', $itemOption->getValue());
                    }

                    foreach ($valueIds as $valueId) {
                        $key = sprintf('config/%d/%d', $optionId, $valueId);
                        $configObj->unsetData($key); //remove custom option oos
                        if (!empty($config[$optionId]) && $config[$optionId][$valueId]) {
                            unset($config[$optionId][$valueId]);
                        }
                    }
                }
            }
        }
        $configObj->setData(
            [
                'config' => $config,
            ]
        );
        return $configObj;
    }

    /**
     * @return Product|null
     */
    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}
