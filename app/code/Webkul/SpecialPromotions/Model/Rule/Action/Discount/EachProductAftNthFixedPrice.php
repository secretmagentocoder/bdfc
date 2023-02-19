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

class EachProductAftNthFixedPrice extends DiscountAbstract
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
       

        $itemPrice              = $this->validator->getItemPrice($item);
        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $productModel           = $this->product->create()->load($item->getProductId());
        $specialPriceFlag       = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag          = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        $childSpecialPriceFlag  = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        
        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }
       

        

            $discountAmt            = $rule->getDiscountAmount();
            $discountStep           = $rule->getDiscountStep();
            $numberOfThreshold      = $rule->getNThreshold();
            $qty                    = $item->getQty();
            $maxDiscountAmount      = $rule->getMaxDiscount();
            $totalCartAmount        = 0;
            $arrayItemsPrice        = [];
            $discountAmount         = 0;
            $count                  = 0;

            if ($numberOfThreshold) {
                $lowestPrice = 0;
                foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
                    if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
                        continue;
                    }
                    $currentQty = $currentItem->getQty();
                    $count = $count + $currentQty;
                    for ($i=1; $i<= $currentQty; $i++) {
                        $arrayItemsPrice [] = [
                            'product_id' => $currentItem->getProductId(),
                            'price' => $currentItem->getPrice()
                        ];
                    }
                    $totalCartAmount = $totalCartAmount+($currentItem->getPrice()*$currentItem->getQty());
                }
                $arrayItemsPrice = $this->sortViaPrice($arrayItemsPrice);
                if ($count > $numberOfThreshold) {
                    $toDiscount = $count - $numberOfThreshold;

                    $discountAmount = $this->getDiscoutCalcted(
                        $arrayItemsPrice,
                        $toDiscount,
                        $discountAmt,
                        $discountAmount,
                        $discountStep,
                        $count
                    );
                }
            }
        
        $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
        if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
            $discountAmount=0;
        } 
        if ($discountAmount < 0) {
            $discountAmount  = $itemPrice;
        }
        
        if ($discountAmount > 0) {
            if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                $discountAmount = $maxDiscountAmount;
            }
            $discountAmount = $discountAmount/$totalCartAmount*($item->getPrice()*$qty);

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
     * @param array $arrayItemsPrice
     * @param integer $toDiscount
     * @param integer $discountAmt
     * @param integer $discountAmount
     * @param integer $discountStep
     * @param integer $cartTotalQty
     * @return integer
     */
    private function getDiscoutCalcted(
        $arrayItemsPrice,
        $toDiscount,
        $discountAmt,
        $discountAmount,
        $discountStep,
        $cartTotalQty
    ) {
        if (!$discountStep) {
            $discountStep = 1;
        }
        $toDiscount = floor($toDiscount / $discountStep);

        $countItem = 0;
        $startArray = $cartTotalQty-$toDiscount;

        if ($toDiscount > 0) {
            foreach ($arrayItemsPrice as $key => $value) {

                $countItem ++;
                if ($countItem > $startArray) {
                    if ($discountAmt < $value['price']) {
                        $discountAmount = $discountAmount + ($value['price'] - $discountAmt) ;
                    }
                }
            }
            return $discountAmount;
        }
        return $discountAmount;
    }

    /**
     * This sorts the array with price
     *
     * @param array $toSort
     * @return array
     */
    public function sortViaPrice($toSort)
    {
        $tempArray = [];
        $numOfValues = count($toSort);
        for ($i=0; $i < ($numOfValues-1); $i++) {
            $position = $i;
            for ($j = $i+1; $j < $numOfValues; $j++) {
                if ($toSort[$position]['price'] < $toSort[$j]['price']) {
                    $position = $j;
                }
            }
            if ($position != $i) {
                $letsSwap = $toSort[$i];
                $toSort[$i] = $toSort[$position];
                $toSort[$position] = $letsSwap;
            }
        }
        return $toSort;
    }
}
