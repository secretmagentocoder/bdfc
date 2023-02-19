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

class EachProductAftNthPercent extends DiscountAbstract
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
        $discountData = $this->discountFactory->create();

        $itemPrice              = $this->validator->getItemPrice($item);
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $productModel           = $this->product->create()->load($item->getProductId());
        $specialPriceFlag       = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $childSpecialPriceFlag  = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        $tierPriceFlag          = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);

        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }
        // $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds(),$qty);
        // if(!in_array($rule->getRuleId(),$highestRuleIds)) {
            
        //     return $discountData;
        // }
        

            $discountStep           = $rule->getDiscountStep();
            $discountAmt            = $rule->getDiscountAmount();
            $nThreshold             = $rule->getNThreshold();
            $qty                    = $item->getQty();
            $discountAmount         = 0;
            $count                  = 0;
            $maxDiscountAmount      = $rule->getMaxDiscount();
            $totalCartAmount        = 0;
            $arrayItemsPrice        = [];

            foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
                if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
                    continue;
                }
                $currentQty = $currentItem->getQty();
                $count = $count + $currentQty;
                for ($i=1; $i <= $currentQty; $i++) {
                    $arrayItemsPrice [] = [
                        'product_id'    => $currentItem->getProductId(),
                        'price'         => $currentItem->getPrice()
                    ];
                }
                $totalCartAmount = $totalCartAmount+($currentItem->getPrice()*$currentItem->getQty());
            }
            $arrayItemsPrice = $this->promotionsHelper->sortViaPrice($arrayItemsPrice);
            if ($count > $nThreshold) {
                $toDiscount = $count - $nThreshold;
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
       
        $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
        if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
            $discountAmount=0;
        } 
        if ($discountAmount > 0) {
            if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                $discountAmount = $maxDiscountAmount;
            }
            $discountAmount = round($discountAmount/$totalCartAmount*($item->getPrice()*$qty), 2);
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }
    
   /**
    * Get Discount
    *
    * @param array $arrayItemsPrice
    * @param int $toDiscount
    * @param int $discountAmt
    * @param int $discountAmount
    * @param int $cartTotalQty
    * @return mixed
    */
    private function getDiscoutCalcted($arrayItemsPrice, $toDiscount, $discountAmt, $discountAmount, $cartTotalQty)
    {
        $countItem = 0;
        $arrayItemsPrice = array_slice($arrayItemsPrice, 0, $toDiscount);
        $startArray = $cartTotalQty-$toDiscount;
        if ($toDiscount > 0) {
            foreach ($arrayItemsPrice as $key => $value) {
                // $countItem ++;
                // if ($countItem > $startArray) {
                    $discountAmount = $discountAmount + ($value["price"] * $discountAmt /100);
                // }
            }
            return $discountAmount;
        }
        return $discountAmount;
    }
   
}
