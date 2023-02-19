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
namespace Webkuls\SpecialPromotions\Model\Rule\Action\Discount;

class CalculatorFactory extends \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory
{
    /**
     * @var array
     */
    protected $classByType = [
        \Magento\SalesRule\Model\Rule::TO_PERCENT_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\ToPercent::class,
        \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\ByPercent::class,
        \Magento\SalesRule\Model\Rule::TO_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ToFixed::class,
        \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ByFixed::class,
        \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed::class,
        \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\BuyXGetY::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_CHEAPEST =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\Cheapest::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_MOST_EXPENSIVE =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\MostExpensive::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_MONEY_AMOUNT =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\MoneyAmount::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_BUY_X_GET_N_PERCENT_DISCOUNT =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\BuyXGetNPercentDiscount::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_BUY_X_GET_N_FIXED_DISCOUNT =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\BuyXGetNFixedDiscount::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_BUY_X_GET_N_FIXED_PRICE =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\BuyXGetNFixedPrice::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_EACH_NTH_PERCENT_DISCOUNT =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\EachNPercentDiscount::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_EACH_NTH_FIXED_DISCOUNT =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\EachNFixedDiscount::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_EACH_NTH_FIXED_PRICE =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\EachNFixedPrice::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_EACH_PAFT_NTH_PERCENT =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\EachProductAftNthPercent::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_EACH_PAFT_NTH_FIXED =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\EachProductAftNthFixed::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_EACH_PAFT_NTH_FIXED_PRICE =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\EachProductAftNthFixedPrice::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_GROUP_N =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\GroupNth::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_GROUP_N_DISCOUNT =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\GroupNPercentDiscount::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_PRODUCT_SET_DISCOUNT =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\ProductSetDiscount::class,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider::WK_PRODUCT_SET_DISCOUNT_FIXED =>
            \Webkuls\SpecialPromotions\Model\Rule\Action\Discount\ProductSetDiscountFixed::class,
    ];
}
