<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Model;

use Bss\Simpledetailconfigurable\Api\Data\ProductDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class ProductData extends AbstractExtensibleModel implements ProductDataInterface
{
    /**
     * @inheritDoc
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritDoc
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritDoc
     */
    public function getStockData()
    {
        return $this->getData(self::STOCK_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setStockData($stockData)
    {
        return $this->setData(self::STOCK_DATA, $stockData);
    }

    /**
     * @inheritDoc
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * @inheritDoc
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * @inheritDoc
     */
    public function getMetaData()
    {
        return $this->getData(self::META_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setMetaData($metaData)
    {
        return $this->setData(self::META_DATA, $metaData);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getPreselect()
    {
        return $this->getData(self::PRESELECT);
    }

    /**
     * @inheritDoc
     */
    public function setPreselect($preselect)
    {
        return $this->setData(self::PRESELECT, $preselect);
    }

    /**
     * @inheritDoc
     */
    public function getGeneralConfig()
    {
        return $this->getData(self::GENERAL_CONFIG);
    }

    /**
     * @inheritDoc
     */
    public function setGeneralConfig($generalConfig)
    {
        return $this->setData(self::GENERAL_CONFIG, $generalConfig);
    }

    /**
     * @inheritDoc
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @inheritDoc
     */
    public function getImages()
    {
        return $this->getData(self::IMAGES);
    }

    /**
     * @inheritDoc
     */
    public function setImages($images)
    {
        return $this->setData(self::IMAGES, $images);
    }

    /**
     * @inheritDoc
     */
    public function getDesc()
    {
        return $this->getData(self::DESC);
    }

    /**
     * @inheritDoc
     */
    public function setDesc($desc)
    {
        return $this->setData(self::DESC, $desc);
    }

    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    /**
     * @inheritDoc
     */
    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalInfo()
    {
        return $this->getData(self::ADDITIONAL_INFO);
    }

    /**
     * @inheritDoc
     */
    public function setAdditionalInfo($items)
    {
        return $this->setData(self::ADDITIONAL_INFO, $items);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Bss\Simpledetailconfigurable\Api\Data\ProductDataExtensionInterface $extension
    ) {
        return $this->_setExtensionAttributes($extension);
    }
}
