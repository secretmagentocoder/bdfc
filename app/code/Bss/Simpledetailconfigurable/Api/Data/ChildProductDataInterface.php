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
namespace Bss\Simpledetailconfigurable\Api\Data;

interface ChildProductDataInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Const
     */
    const ENTITY = 'entity';
    const SKU = 'sku';
    const NAME = 'name';
    const DESC = 'desc';
    const TIER_PRICE = 'tier_price';
    const STOCK_DATA = 'stock_data';
    const IMAGES = 'images';
    const META_DATA = 'meta_data';
    const ADDITIONAL_INFO = 'additional_info';
    const REVIEW_COUNT = 'review_count';
    const REVIEWS = 'reviews';

    /**
     * @return int
     */
    public function getEntity();

    /**
     * @param int $id
     * @return $this
     */
    public function setEntity($id);

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDesc();

    /**
     * @param string $desc
     * @return $this
     */
    public function setDesc($desc);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\TierPriceInterface[]
     */
    public function getTierPrices();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\TierPriceInterface[] $tiers
     * @return $this
     */
    public function setTierPrices($tier);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\StockDataInterface
     */
    public function getStockData();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\StockDataInterface $stockData
     * @return $this
     */
    public function setStockData($stockData);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\ImageDataInterface[]
     */
    public function getImages();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\StockDataInterface[] $images
     * @return $this
     */
    public function setImages($images);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\MetaDataInterface
     */
    public function getMetaData();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\MetaDataInterface $metaData
     * @return $this
     */
    public function setMetaData($metaData);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\AdditionalInfoInterface[]
     */
    public function getAdditionalInfo();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\AdditionalInfoInterface[] $items
     * @return $this
     */
    public function setAdditionalInfo($items);

    /**
     * @return int
     */
    public function getReviewCount();

    /**
     * @param int $count
     * @return $this
     */
    public function setReviewCount($count);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\ReviewInterface[]
     */
    public function getReviews();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\ReviewInterface[] $reviews
     * @return $this
     */
    public function setReviews($reviews);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\ChildProductDataExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\ChildProductDataExtensionInterface $extension
     * @return $this
     */
    public function setExtensionAttributes(
        \Bss\Simpledetailconfigurable\Api\Data\ChildProductDataExtensionInterface $extension
    );
}
