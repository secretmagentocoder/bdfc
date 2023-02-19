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

use Bss\Simpledetailconfigurable\Api\Data\AttributesSelectInterface;
use Magento\Framework\DataObject;

class AttributesSelect extends DataObject implements AttributesSelectInterface
{
    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->getData(self::ATTRIBUTE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode($code)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->getData(self::ATTRIBUTE_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(self::ATTRIBUTE_VALUE, $value);
    }
}
