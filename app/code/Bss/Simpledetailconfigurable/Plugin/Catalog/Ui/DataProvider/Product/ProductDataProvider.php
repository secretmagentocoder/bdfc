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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Plugin\Catalog\Ui\DataProvider\Product;

use Bss\Simpledetailconfigurable\Override\Catalog\Model\Product\Visibility;

class ProductDataProvider
{
    /**
     * Change value visibility in product grid.
     *
     * @param \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(\Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject, $result)
    {
        try {
            foreach ($result['items'] as &$item) {
                if (!empty($item['only_display_product_page'])) {
                    $item['visibility'] = Visibility::VISIBILITY_REDIRECT;
                }
            }

            return $result;
        } catch (\Exception $e) {
            return $result;
        }
    }
}
