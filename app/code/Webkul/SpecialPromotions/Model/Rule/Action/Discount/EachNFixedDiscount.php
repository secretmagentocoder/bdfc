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

class EachNFixedDiscount extends DiscountAbstract
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
        $discountData           = $this->discountFactory->create();
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $productModel           = $this->product->create()->load($item->getProductId());
        $specialPriceFlag       = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag          = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        $itemPrice              = $this->validator->getItemPrice($item);
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $childSpecialPriceFlag  = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        $qty                = $item->getQty();
        $discountAmount     = 0;
       

            $maxDiscountAmount  = $rule->getMaxDiscount();
            $discountStep       = $rule->getDiscountStep();
            $discountAmountRule = $rule->getDiscountAmount();
            
            if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
                return $discountData;
            }
           
            if ($discountStep) {
                for ($i=1; $i <= $qty; $i++) {
                    if ($i % $discountStep == 0) {
                        if ($discountAmountRule <= $itemPrice) {
                            $discountAmount =  $discountAmount+$discountAmountRule;
                        }
                    }
                }
            }

      
        $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
        if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
            $discountAmount=0;
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

    /**
     * To get Highest Priority Rule
     *
     * @param string $ids
     * @return array
     */
    public function getHighestPriorityRule($ids) {
        
        $arrayRules = [];
        $ruleId =[];
        if ($ids!='') {
            $ids = explode(',', $ids);
            $arrayRules = $this->promotionsHelper->getRuleDetailsByIds($ids);
        }
        if (!empty($arrayRules)) {
            $ruleId = array_keys($arrayRules, min($arrayRules));
            
        }
        return $ruleId;
    }
}
