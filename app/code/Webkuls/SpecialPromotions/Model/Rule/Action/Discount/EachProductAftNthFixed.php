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

use \Magento\SalesRule\Model\Rule\Action\Discount;

class EachProductAftNthFixed extends DiscountAbstract
{
    /**
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
        $discountStep           = $rule->getDiscountStep();
        $discountAmt            = $rule->getDiscountAmount();
        $numberOfThreshold      = $rule->getNThreshold();
        $qty                    = $item->getQty();
        $arrayItemsPrice        = [];
        $discountAmount         = 0;
        $count                  = 0;
        $maxDiscountAmount      = $rule->getMaxDiscount();
        
        if ($numberOfThreshold) {
            $lowestPrice = 0;
            foreach ($this->cart->getQuote()->getAllVisibleItems() as $currentItem) {
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
            }
            $arrayItemsPrice = $this->sortViaPrice($arrayItemsPrice);
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
                    $discountAmount
                );
            }
        }
        if ($discountAmount > 0) {
            if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                $discountAmount = $maxDiscountAmount;
            }
            $discountAmount = $discountAmount/ $count * $qty;
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
     * @return integer
     */
    private function getDiscoutCalcted($arrayItemsPrice, $toDiscount, $discountAmt, $discountAmount)
    {
        $itemCount = 0;
        if ($toDiscount > 0) {
            foreach ($arrayItemsPrice as $key => $value) {
                $itemCount++;
                if ($itemCount <= $toDiscount) {
                    $discountAmount = max($discountAmount, 0);
                    $discountAmount = $discountAmount + $discountAmt;
                }
            }
            return $discountAmount;
        }
        return $discountAmount;
    }

    /**
     * Sort as per price
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
                if ($toSort[$position]['price'] > $toSort[$j]['price']) {
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
