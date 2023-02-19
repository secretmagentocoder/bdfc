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

use Magento\Framework\DataObject;
use Bss\Simpledetailconfigurable\Api\Data\ConfigurationInterface;

class Configuration extends DataObject implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getEnabled()
    {
        return $this->getData(self::ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function setEnabled($isEnabled)
    {
        return $this->setData(self::ENABLED, $isEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getDisplaySku()
    {
        return $this->getData(self::DISPLAY_SKU);
    }

    /**
     * @inheritDoc
     */
    public function setDisplaySku($displaySku)
    {
        return $this->setData(self::DISPLAY_SKU, $displaySku);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayName()
    {
        return $this->getData(self::DISPLAY_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayName($displayName)
    {
        return $this->setData(self::DISPLAY_NAME, $displayName);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayDesc()
    {
        return $this->getData(self::DISPLAY_DESC);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayDesc($displayDesc)
    {
        return $this->setData(self::DISPLAY_DESC, $displayDesc);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayTierPrice()
    {
        return $this->getData(self::DISPLAY_TIER);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayTierPrice($displayTierPrice)
    {
        return $this->setData(self::DISPLAY_TIER, $displayTierPrice);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayStock()
    {
        return $this->getData(self::DISPLAY_STOCK);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayStock($displayStock)
    {
        return $this->setData(self::DISPLAY_STOCK, $displayStock);
    }

    /**
     * @inheritDoc
     */
    public function getGallerySwitchStrategy()
    {
        return $this->getData(self::DISPLAY_IMAGES);
    }

    /**
     * @inheritDoc
     */
    public function setGallerySwitchStrategy($displayImage)
    {
        return $this->setData(self::DISPLAY_IMAGES, $displayImage);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayChildImages()
    {
        return $this->getData(self::DISPLAY_CHILD_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayChildImages($displayImage)
    {
        return $this->setData(self::DISPLAY_CHILD_IMAGE, $displayImage);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayAdditionalInfo()
    {
        return $this->getData(self::DISPLAY_ADDITIONAL_INFO);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayAdditionalInfo($displayAdditionalInfo)
    {
        return $this->setData(self::DISPLAY_ADDITIONAL_INFO, $displayAdditionalInfo);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayMetaData()
    {
        return $this->getData(self::DISPLAY_META_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayMetaData($displayMetaData)
    {
        return $this->setData(self::DISPLAY_META_DATA, $displayMetaData);
    }

    /**
     * @inheritDoc
     */
    public function getDisplayChildOptions()
    {
        return $this->getData(self::DISPLAY_CHILD_OPTIONS);
    }

    /**
     * @inheritDoc
     */
    public function setDisplayChildOptions($displayChildOptions)
    {
        return $this->setData(self::DISPLAY_CHILD_OPTIONS, $displayChildOptions);
    }

    /**
     * @inheritDoc
     */
    public function getEnableCustomUrl()
    {
        return $this->getData(self::ENABLE_CUSTOM_URL);
    }

    /**
     * @inheritDoc
     */
    public function setEnableCustomUrl($isEnabled)
    {
        return $this->setData(self::ENABLE_CUSTOM_URL, $isEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getEnablePreselect()
    {
        return $this->getData(self::ENABLE_PRESELECT);
    }

    /**
     * @inheritDoc
     */
    public function setEnablePreselect($isEnabled)
    {
        return $this->setData(self::ENABLE_PRESELECT, $isEnabled);
    }

    /**
     * @inheritDoc
     */
    public function getEnableUrlSitemap()
    {
        return $this->getData(self::ENABLE_SITEMAP);
    }

    /**
     * @inheritDoc
     */
    public function setEnableUrlSitemap($isEnabled)
    {
        return $this->setData(self::ENABLE_SITEMAP, $isEnabled);
    }
}
