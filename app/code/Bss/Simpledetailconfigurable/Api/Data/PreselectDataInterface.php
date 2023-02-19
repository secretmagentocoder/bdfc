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

interface PreselectDataInterface
{
    /**
     * Const
     */
    const ATTRIBUTE_ID = 'attribute_id';
    const SELECTED_VALUE = 'selected_value';

    /**
     * @return int
     */
    public function getAttributeId();

    /**
     * @param int $attributeId
     * @return $this
     */
    public function setAttributeId($attributeId);

    /**
     * @return mixed
     */
    public function getSelectedValue();

    /**
     * @param mixed $value
     * @return $this
     */
    public function setSelectedValue($value);
}
