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

interface ProductDataInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Const
     */
    const ENABLE_MODULE = 'enable_module';
    const ENABLE_AJAX_LOAD = 'enable_ajax_load';
    const SKU = 'sku';
    const STOCK_DATA = 'stock_data';
    const URL = 'url';
    const META_DATA = 'meta_data';
    const NAME = 'name';
    const PRESELECT = 'preselect';
    const GENERAL_CONFIG = 'general_config';
    const PRICE = 'price';
    const IMAGES = 'images';
    const DESC = 'desc';
    const ITEMS = 'items';
    const ADDITIONAL_INFO = 'additional_info';

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
     * @return \Bss\Simpledetailconfigurable\Api\Data\StockDataInterface
     */
    public function getStockData();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\StockDataInterface $stockData
     * @return $this
     */
    public function setStockData($stockData);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

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
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\PreselectDataInterface[]
     */
    public function getPreselect();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\PreselectDataInterface[] $preselect
     * @return $this
     */
    public function setPreselect($preselect);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\GeneralConfigInterface
     */
    public function getGeneralConfig();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\GeneralConfigInterface $generalConfig
     * @return $this
     */
    public function setGeneralConfig($generalConfig);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return \Bss\Simpledetailconfigurable\Api\Data\ImageDataInterface[]
     */
    public function getImages();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\PreselectDataInterface[] $images
     * @return $this
     */
    public function setImages($images);

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
     * @return \Bss\Simpledetailconfigurable\Api\Data\ChildProductDataInterface[]
     */
    public function getItems();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\ChildProductDataInterface[] $items
     * @return $this
     */
    public function setItems($items);

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
     * @return \Bss\Simpledetailconfigurable\Api\Data\ProductDataExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\ProductDataExtensionInterface $extension
     * @return $this
     */
    public function setExtensionAttributes(
        \Bss\Simpledetailconfigurable\Api\Data\ProductDataExtensionInterface $extension
    );
}
