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
use Bss\Simpledetailconfigurable\Api\Data\PreselectDataInterface;

class PreselectData extends DataObject implements PreselectDataInterface
{
    /**
     * @inheritDoc
     */
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * @inheritDoc
     */
    public function getSelectedValue()
    {
        return $this->getData(self::SELECTED_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setSelectedValue($value)
    {
        return $this->setData(self::SELECTED_VALUE, $value);
    }
}
