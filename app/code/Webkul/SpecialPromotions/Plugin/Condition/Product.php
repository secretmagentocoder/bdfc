<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SpecialPromotions
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace  Webkul\SpecialPromotions\Plugin\Condition;

class Product
{

    /**
     * Condition After
     *
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @return \Magento\Rule\Model\Condition\Product\AbstractProduct
     */
    public function afterLoadAttributeOptions(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
    ) {
        $attributes = [];
        $attributes['quote_item_sku'] = __('Custom Options SKU');
  
        $subject->setAttributeOption(array_merge($subject->getAttributeOption(), $attributes));
        return $subject;
    }
}
