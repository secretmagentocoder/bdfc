<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Helper;

use Magento\Catalog\Model\Product as ProductModel;
use \Magento\CatalogInventory\Api\StockRegistryInterface as StockRegistry;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Event\ManagerInterface;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

/**
 * OptionInventory Stock Helper.
 *
 * @package MageWorx\OptionInventory\Helper
 */
class Stock extends \Magento\Framework\App\Helper\AbstractHelper
{

    const MANAGE_STOCK_ENABLED  = '1';
    const MANAGE_STOCK_DISABLED = '0';

    /**
     * Product model
     *
     * @var ProductModel
     */
    protected $product;

    /**
     * @var StockRegistry
     */
    protected $stockRegistry;

    /**
     * OptionInventory Data Helper
     *
     * @var Data
     */
    protected $helperData;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * Stock constructor.
     *
     * @param Data $helperData
     * @param ProductModel $product
     * @param StockRegistry $stockRegistry
     * @param Context $context
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        \MageWorx\OptionInventory\Helper\Data $helperData,
        ProductModel $product,
        StockRegistry $stockRegistry,
        Context $context,
        BaseHelper $baseHelper
    ) {

        $this->helperData    = $helperData;
        $this->product       = $product;
        $this->stockRegistry = $stockRegistry;
        $this->baseHelper    = $baseHelper;
        parent::__construct($context);
    }

    /**
     * Check if option value is out of stock
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @return bool
     */
    public function isOutOfStockOption($value)
    {
        $manageStock = $value->getManageStock();
        $valueSku    = $value->getSku();

        if ($this->baseHelper->isModuleEnabled('Magento_InventorySalesAdminUi') && $this->validateSku($value)) {
            $qty = $this->baseHelper->updateValueQtyToSalableQty($valueSku);
        } else {
            $qty = $value->getQty();
        }

        if (!$manageStock) {
            return false;
        }

        if ($qty <= 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if sku is valid
     *
     * @param $value
     * @return bool
     */
    protected function validateSku($value): bool
    {
        if (isset($value['sku_is_valid']) && $value['sku_is_valid']) {
            return true;
        }
        return false;
    }

    /**
     * Floating option value qty
     *
     * @param int|float $qty
     * @param int $productId
     * @param null|\Magento\Catalog\Model\Product $product
     * @return float|int
     */
    public function floatingQty($qty, $productId, $product = null)
    {
        if ($this->isTemplateGroup($productId, $product)) {
            return (float)$qty;
        }

        if ($product) {
            $this->product = $product;
        } elseif (!$this->product) {
            $this->product->load($productId);
        }

        $stockData = $this->product->getStockData();

        if ($stockData) {
            $isQtyDecimal = is_array($stockData)
                ? (bool)$stockData['is_qty_decimal']
                : (bool)$stockData->getIsQtyDecimal();
        } else {
            $stockData = $this->stockRegistry->getStockItem(
                $this->product->getId(),
                $this->product->getStore()->getWebsiteId()
            );
            $this->product->setStockData($stockData);

            $isQtyDecimal = (bool)$stockData->getIsQtyDecimal();
        }

        return $isQtyDecimal ? (float)$qty : (int)$qty;
    }

    /**
     * Set stock message to xpath element
     *
     * @param \DOMDocument $dom
     * @param \DOMElement $elementTitle
     * @param string $stockMessage
     */
    public function setStockMessage($dom, $elementTitle, $stockMessage = '')
    {
        $elementTitle->nodeValue = htmlentities($elementTitle->nodeValue . $stockMessage);
    }

    /**
     * Retrieve stock message
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @return string
     */
    public function getStockMessage($value, $productId): string
    {
        $stockMessage = '';

        $isDisplayOptionInventory   = $this->helperData->isDisplayOptionInventoryOnFrontend();
        $isDisplayOutOfStockMessage = $this->helperData->isDisplayOutOfStockMessage();

        $valueManageStock = $value->getManageStock();
        if (!$valueManageStock) {
            return $stockMessage;
        }

        $valueQty          = $this->floatingQty($value->getQty(), $productId);
        $inventoryMessage  = '(' . $valueQty . ')';
        $outOfStockMessage = '(' . __('Out Of Stock') . ')';

        if ($isDisplayOutOfStockMessage) {
            $stockMessage .= !$this->isOutOfStockOption($value) && $isDisplayOptionInventory ? $inventoryMessage : '';
            $stockMessage .= $this->isOutOfStockOption($value) ? $outOfStockMessage : '';
        } else {
            $stockMessage .= $isDisplayOptionInventory ? $inventoryMessage : '';
        }

        return (string)$stockMessage;
    }

    /**
     * Disable option value
     *
     * @param \DOMElement $element
     */
    public function disableOutOfStockOption($element)
    {
        if ($element) {
            $element->setAttribute('disabled', 'disabled');
        }
    }

    /**
     * Hide option value
     *
     * @param \DOMElement $element
     */
    public function hideOutOfStockOption($element)
    {
        if ($element) {
            $element->parentNode->removeChild($element);
        }
    }

    /**
     * Retrieve options values id from requested data
     *
     * @param array $options
     * @return array
     */
    public function getRequestedValuesId($options)
    {
        $valuesId = [];

        array_walk_recursive(
            $options,
            function ($value, $key) use (&$valuesId) {
                if ($value) {
                    $valuesId[] = $value;
                }
            }
        );

        return $valuesId;
    }

    /**
     * Retrieve options values id from product options
     *
     * @param array $options
     * @return array
     */
    public function getOptionValuesId($options)
    {
        $optionValuesId = [];

        foreach ($options as $optionId => $values) {
            if (!is_array($values)) {
                $values = [$values => []];
            }
            $optionValuesId = array_merge($optionValuesId, array_keys($values));
        }

        return $optionValuesId;
    }

    /**
     * If product is null and productId is null
     * then it's template group
     *
     * @param null|int $productId
     * @param null|\Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function isTemplateGroup($productId, $product)
    {
        return !$productId && !$product;
    }
}
