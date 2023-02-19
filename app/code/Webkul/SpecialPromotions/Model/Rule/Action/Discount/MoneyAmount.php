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
namespace Webkul\SpecialPromotions\Model\Rule\Action\Discount;

use \Magento\SalesRule\Model\Rule\Action\Discount;

class MoneyAmount extends DiscountAbstract
{
    /**
     * Calculate Discount
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData           = $this->discountFactory->create();
        $itemPrice              = $this->validator->getItemPrice($item);
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $proCount               = 0;
        $currentTotal           = $itemPrice;
        $totalPrice             = 0;
        $productModel           = $this->product->create()->load($item->getProductId());
        $specialPriceFlag       = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag          = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        $childSpecialPriceFlag  = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        $discountStep           = $rule->getDiscountStep();
        $discountAmount         = $rule->getDiscountAmount();
        $maxDiscountAmount      = $rule->getMaxDiscount();
        
        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }
        
        if ($discountStep == 0 || $currentTotal == 0) {
            return $discountData;
        }
        // $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds(),$qty);
        // if(!in_array($rule->getRuleId(),$highestRuleIds)) {
            
        //     return $discountData;
        // }

        foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
            if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
                continue;
            }
                $productPrice   = $currentItem->getPrice();
                $productQty     = $currentItem->getQty();
                $totalPrice     = $totalPrice+$productPrice*$productQty;
                $proCount++;
        }
         
        $discountQty = floor($totalPrice / $discountStep);
        $discountAmount = ($discountQty * $discountAmount);
        if ($discountAmount > 0) {
            if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                $discountAmount = $maxDiscountAmount;
            }
            
            $discountAmount = round($discountAmount/$totalPrice*($item->getPrice()*$qty), 2);
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }
}
