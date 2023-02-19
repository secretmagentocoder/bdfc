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

interface StockDataInterface
{
    /**
     * Const
     */
    const IS_IN_STOCK = 'is_in_stock';
    const SALABLE_QTY = 'salable_qty';

    /**
     * @return bool
     */
    public function getIsInStock();

    /**
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock);

    /**
     * @return float
     */
    public function getSalableQty();

    /**
     * @param float $qty
     * @return $this
     */
    public function setSalableQty($qty);
}
