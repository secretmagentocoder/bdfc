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

use Bss\Simpledetailconfigurable\Api\Data\StockDataInterface;
use Magento\Framework\DataObject;

class StockData extends DataObject implements StockDataInterface
{
    /**
     * @inheritDoc
     */
    public function getIsInStock()
    {
        return $this->getData(self::IS_IN_STOCK);
    }

    /**
     * @inheritDoc
     */
    public function setIsInStock($isInStock)
    {
        return $this->setData(self::IS_IN_STOCK, $isInStock);
    }

    /**
     * @inheritDoc
     */
    public function getSalableQty()
    {
        return $this->getData(self::SALABLE_QTY);
    }

    /**
     * @inheritDoc
     */
    public function setSalableQty($qty)
    {
        return $this->setData(self::SALABLE_QTY, $qty);
    }
}
