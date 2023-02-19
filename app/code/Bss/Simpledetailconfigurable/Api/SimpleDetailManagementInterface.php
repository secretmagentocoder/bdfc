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
namespace Bss\Simpledetailconfigurable\Api;

interface SimpleDetailManagementInterface
{
    /**
     * @param int $storeId
     * @return \Bss\Simpledetailconfigurable\Api\Data\ConfigurationInterface
     */
    public function getConfiguration($storeId);

    /**
     * @param string $sku
     * @param int $storeId
     * @return \Bss\Simpledetailconfigurable\Api\Data\ProductDataInterface
     */
    public function getProductData($sku, $storeId);

    /**
     * @param \Bss\Simpledetailconfigurable\Api\Data\AttributesSelectInterface[] $attributeSelect
     * @param string $sku
     * @param int $storeId
     * @return \Bss\Simpledetailconfigurable\Api\Data\ChildProductDataInterface
     */
    public function getPreselect($attributeSelect, $sku, $storeId);

    /**
     * @param string $sku
     * @param int $storeId
     * @return \Bss\Simpledetailconfigurable\Api\Data\ChildProductDataInterface
     */
    public function getPreselectConfig($sku, $storeId);
}
