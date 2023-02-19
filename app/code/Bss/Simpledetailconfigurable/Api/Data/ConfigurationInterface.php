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

interface ConfigurationInterface
{
    /**
     * Const
     */
    const ENABLED = 'is_enabled';
    const DISPLAY_SKU = 'display_sku';
    const DISPLAY_NAME = 'display_name';
    const DISPLAY_DESC = 'display_desc';
    const DISPLAY_TIER = 'display_tier_price';
    const DISPLAY_STOCK = 'display_stock';
    const DISPLAY_IMAGES = 'display_images';
    const DISPLAY_CHILD_IMAGE = 'display_child_image';
    const DISPLAY_ADDITIONAL_INFO = 'display_additional_info';
    const DISPLAY_META_DATA = 'display_meta_data';
    const DISPLAY_CHILD_OPTIONS = 'display_child_options';
    const ENABLE_CUSTOM_URL = 'enable_custom_url';
    const ENABLE_PRESELECT = 'enable_preselect';
    const ENABLE_SITEMAP = 'enable_url_sitemap';

    const MAP = [
        'setEnabled' => 'Bss_Commerce/Simpledetailconfigurable/Enable',
        'setDisplaySku' => 'Bss_Commerce/SDCP_details/sku',
        'setDisplayName' => 'Bss_Commerce/SDCP_details/name',
        'setDisplayDesc' => 'Bss_Commerce/SDCP_details/desc',
        'setDisplayTierPrice' => 'Bss_Commerce/SDCP_details/tier_price',
        'setDisplayStock' => 'Bss_Commerce/SDCP_details/stock',
        'setDisplayImages' => 'Bss_Commerce/SDCP_details/image',
        'setDisplayChildImages' => 'Bss_Commerce/SDCP_details/child_image',
        'setDisplayAdditionalInfo' => 'Bss_Commerce/SDCP_details/additional_info',
        'setDisplayMetaData' => 'Bss_Commerce/SDCP_details/meta_data',
        'setDisplayChildOptions' => 'Bss_Commerce/SDCP_details/child_options',
        'setEnableCustomUrl' => 'Bss_Commerce/SDCP_advanced/url',
        'setEnablePreselect' => 'Bss_Commerce/SDCP_advanced/preselect',
        'setEnableUrlSitemap' => 'Bss_Commerce/SDCP_advanced/url_sitemap',
    ];

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setEnabled($isEnabled);

    /**
     * @return bool
     */
    public function getDisplaySku();

    /**
     * @param bool $displaySku
     * @return $this
     */
    public function setDisplaySku($displaySku);

    /**
     * @return bool
     */
    public function getDisplayName();

    /**
     * @param bool $displayName
     * @return $this
     */
    public function setDisplayName($displayName);

    /**
     * @return bool
     */
    public function getDisplayDesc();

    /**
     * @param bool $displayDesc
     * @return $this
     */
    public function setDisplayDesc($displayDesc);

    /**
     * @return bool
     */
    public function getDisplayTierPrice();

    /**
     * @param bool $displayTierPrice
     * @return $this
     */
    public function setDisplayTierPrice($displayTierPrice);

    /**
     * @return bool
     */
    public function getDisplayStock();

    /**
     * @param bool $displayStock
     * @return $this
     */
    public function setDisplayStock($displayStock);

    /**
     * @return string
     */
    public function getGallerySwitchStrategy();

    /**
     * @param string $displayImage
     * @return $this
     */
    public function setGallerySwitchStrategy($displayImage);

    /**
     * @return string
     */
    public function getDisplayChildImages();

    /**
     * @param string $displayImage
     * @return $this
     */
    public function setDisplayChildImages($displayImage);

    /**
     * @return bool
     */
    public function getDisplayAdditionalInfo();

    /**
     * @param bool $displayAdditionalInfo
     * @return $this
     */
    public function setDisplayAdditionalInfo($displayAdditionalInfo);

    /**
     * @return bool
     */
    public function getDisplayMetaData();

    /**
     * @param bool $displayMetaData
     * @return $this
     */
    public function setDisplayMetaData($displayMetaData);

    /**
     * @return bool
     */
    public function getDisplayChildOptions();

    /**
     * @param bool $displayChildOptions
     * @return $this
     */
    public function setDisplayChildOptions($displayChildOptions);

    /**
     * @return bool
     */
    public function getEnableCustomUrl();

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setEnableCustomUrl($isEnabled);

    /**
     * @return bool
     */
    public function getEnablePreselect();

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setEnablePreselect($isEnabled);

    /**
     * @return bool
     */
    public function getEnableUrlSitemap();

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setEnableUrlSitemap($isEnabled);
}
