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
namespace Bss\Simpledetailconfigurable\Override\Catalog\Model\Product;

class Visibility extends \Magento\Catalog\Model\Product\Visibility
{
    const VISIBILITY_REDIRECT = 5;

    /**
     * Retrieve all options
     *
     * @return array
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Retrieve all options
     *
     * @return array
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getAllOptions()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Retrieve option text
     *
     * @param int $optionId
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return $options[$optionId] ?? null;
    }

    /**
     * Add new option in visibility.
     *
     * @return array
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getOptionArray()
    {
        return [
            self::VISIBILITY_NOT_VISIBLE => __('Not Visible Individually'),
            self::VISIBILITY_IN_CATALOG => __('Catalog'),
            self::VISIBILITY_IN_SEARCH => __('Search'),
            self::VISIBILITY_BOTH => __('Catalog, Search'),
            self::VISIBILITY_REDIRECT => __('Only display product page')
        ];
    }
}
