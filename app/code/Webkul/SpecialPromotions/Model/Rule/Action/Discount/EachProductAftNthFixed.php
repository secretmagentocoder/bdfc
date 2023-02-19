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

class EachProductAftNthFixed extends DiscountAbstract
{
    /**
     * Initialize
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
        
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemPrice = $this->validator->getItemPrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
        $productModel = $this->product->create()->load($item->getProductId());
        $specialPriceFlag = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        $childSpecialPriceFlag = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );

        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }
        // $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds(),$qty);
        // if(!in_array($rule->getRuleId(),$highestRuleIds)) {
            
        //     return $discountData;
        // }
        

            $discountStep = $rule->getDiscountStep();
            $discountAmt = $rule->getDiscountAmount();
            $numberOfThreshold = $rule->getNThreshold();

            $qty = $item->getQty();
            $arrayItemsPrice = [];
            $discountAmount = 0;
            $count = 0;
            $maxDiscountAmount = $rule->getMaxDiscount();

            if ($numberOfThreshold) {
                $lowestPrice = 0;
                foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
                    if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
                        continue;
                    }
                    $currentQty = $currentItem->getQty();
                    $count = $count + $currentQty;
                    for ($i = 1; $i <= $currentQty; $i++) {
                        $arrayItemsPrice[] = [
                            'product_id' => $currentItem->getProductId(),
                            'price' => $currentItem->getPrice(),
                        ];
                    }
                }

                $arrayItemsPrice = $this->promotionsHelper->sortViaPrice($arrayItemsPrice);

                if ($count > $numberOfThreshold) {
                    $toDiscount = $count - $numberOfThreshold;
                    if (!$discountStep) {
                        $discountStep = 1;
                    }
                    $toDiscount = floor($toDiscount / $discountStep);
                    $discountAmount = $this->getDiscoutCalcted(
                        $arrayItemsPrice,
                        $toDiscount,
                        $discountAmt,
                        $discountAmount,
                        $count
                    );
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
            $discountAmount = $discountAmount / $count * $qty;

            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }

        return $discountData;
    }

    /**
     * Discount amount calculation
     *
     * @param array   $arrayItemsPrice
     * @param integer $toDiscount
     * @param integer $discountAmt
     * @param integer $discountAmount
     * @param int     $cartTotalQty
     * @return integer
     */
    private function getDiscoutCalcted($arrayItemsPrice, $toDiscount, $discountAmt, $discountAmount, $cartTotalQty)
    {
        $itemCount = 0;
        $startArray = $cartTotalQty - $toDiscount;
        if ($toDiscount > 0) {
            foreach ($arrayItemsPrice as $key => $value) {
                $itemCount++;
                if ($itemCount > $startArray) {
                    $discountAmount = max($discountAmount, 0);
                    if ($discountAmt <= $value['price']) {
                        $discountAmount = $discountAmount + $discountAmt;
                    }
                }
            }
            return $discountAmount;
        }
        return $discountAmount;
    }
   
}
