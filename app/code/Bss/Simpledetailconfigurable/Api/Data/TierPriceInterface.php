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

interface TierPriceInterface
{
    const QTY = 'qty';
    const FINAL = 'final';
    const VALUE = 'value';
    const BASE = 'base';
    const FINAL_DISCOUNT = 'final_discount';
    const BASE_DISCOUNT = 'base_discount';
    const PERCENT = 'percent';

    /**
     * @return float
     */
    public function getQty();

    /**
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * @return float
     */
    public function getFinal();

    /**
     * @param float $finalPrice
     * @return $this
     */
    public function setFinal($finalPrice);

    /**
     * @return float
     */
    public function getValue();

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return float
     */
    public function getBase();

    /**
     * @param float $basePrice
     * @return $this
     */
    public function setBase($basePrice);

    /**
     * @return float
     */
    public function getFinalDiscount();

    /**
     * @param float $finalDiscount
     * @return $this
     */
    public function setFinalDiscount($finalDiscount);

    /**
     * @return float
     */
    public function getBaseDiscount();

    /**
     * @param float $baseDiscount
     * @return $this
     */
    public function setBaseDiscount($baseDiscount);

    /**
     * @return float
     */
    public function getPercent();

    /**
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent);
}
