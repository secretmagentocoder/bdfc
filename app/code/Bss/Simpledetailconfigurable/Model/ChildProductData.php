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

use Magento\Framework\Model\AbstractExtensibleModel;
use Bss\Simpledetailconfigurable\Api\Data\ChildProductDataInterface;

class ChildProductData extends AbstractExtensibleModel implements ChildProductDataInterface
{
    /**
     * @inheritDoc
     */
    public function getEntity()
    {
        return $this->getData(self::ENTITY);
    }

    /**
     * @inheritDoc
     */
    public function setEntity($id)
    {
        return $this->setData(self::ENTITY, $id);
    }

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
    public function getTierPrices()
    {
        return $this->getData(self::TIER_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setTierPrices($tiers)
    {
        return $this->setData(self::TIER_PRICE, $tiers);
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
    public function getReviewCount()
    {
        return $this->getData(self::REVIEW_COUNT);
    }

    /**
     * @inheritDoc
     */
    public function setReviewCount($count)
    {
        return $this->setData(self::REVIEW_COUNT, $count);
    }

    /**
     * @inheritDoc
     */
    public function getReviews()
    {
        return $this->getData(self::REVIEWS);
    }

    /**
     * @inheritDoc
     */
    public function setReviews($reviews)
    {
        return $this->setData(self::REVIEWS, $reviews);
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
        \Bss\Simpledetailconfigurable\Api\Data\ChildProductDataExtensionInterface $extension
    ) {
        return $this->_setExtensionAttributes($extension);
    }
}
