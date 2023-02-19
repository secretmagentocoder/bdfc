<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */
namespace  Webkuls\SpecialPromotions\Model\Rule\Condition;

class Product extends \Magento\SalesRule\Model\Rule\Condition\Product
{
    /**
     * Add special attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['quote_item_sku'] = __('Custom Options SKU');
        parent::_addSpecialAttributes($attributes);
    }
}
