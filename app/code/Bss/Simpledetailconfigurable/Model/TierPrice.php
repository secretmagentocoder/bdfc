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

use Bss\Simpledetailconfigurable\Api\Data\TierPriceInterface;
use Magento\Framework\DataObject;

class TierPrice extends DataObject implements TierPriceInterface
{
    /**
     * @inheritDoc
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * @inheritDoc
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * @inheritDoc
     */
    public function getFinal()
    {
        return $this->getData(self::FINAL);
    }

    /**
     * @inheritDoc
     */
    public function setFinal($finalPrice)
    {
        return $this->setData(self::FINAL, $finalPrice);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getBase()
    {
        return $this->getData(self::BASE);
    }

    /**
     * @inheritDoc
     */
    public function setBase($basePrice)
    {
        return $this->setData(self::BASE, $basePrice);
    }

    /**
     * @inheritDoc
     */
    public function getFinalDiscount()
    {
        return $this->getData(self::FINAL_DISCOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setFinalDiscount($finalDiscount)
    {
        return $this->setData(self::FINAL_DISCOUNT, $finalDiscount);
    }

    /**
     * @inheritDoc
     */
    public function getBaseDiscount()
    {
        return $this->getData(self::BASE_DISCOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBaseDiscount($baseDiscount)
    {
        return $this->setData(self::BASE_DISCOUNT, $baseDiscount);
    }

    /**
     * @inheritDoc
     */
    public function getPercent()
    {
        return $this->getData(self::PERCENT);
    }

    /**
     * @inheritDoc
     */
    public function setPercent($percent)
    {
        return $this->setData(self::PERCENT, $percent);
    }
}
