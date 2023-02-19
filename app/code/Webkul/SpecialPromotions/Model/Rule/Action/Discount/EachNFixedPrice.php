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

class EachNFixedPrice extends DiscountAbstract
{
    /**
     * Calculate
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $itemPrice              = $this->validator->getItemPrice($item);
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $productModel           = $this->product->create()->load($item->getProductId());
        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $specialPriceFlag       = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag          = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        $childSpecialPriceFlag  = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        

            $discountStep           = $rule->getDiscountStep();
            $discountAmountRule     = $rule->getDiscountAmount();
            $qty                    = $item->getQty();
            $discountAmount         = 0;
            $maxDiscountAmount      = $rule->getMaxDiscount();
            
            if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
                return $discountData;
            }
            
            if ($discountStep) {
                for ($i=1; $i <= $qty; $i++) {
                    if ($i % $discountStep == 0) {
                        if ($discountAmountRule < $itemPrice) {
                            $discountAmount = $discountAmount + ($itemPrice - $discountAmountRule);
                        }
                    }
                }
            }
        $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
        if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
            $discountAmount=0;
        } 
        if ($discountAmount < 0) {
            $discountAmount = $itemPrice * $qty;
        }
        if ($discountAmount > 0) {
            if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                $discountAmount = $maxDiscountAmount;
            }
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }
    
}
