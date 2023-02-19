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

class ProductSetDiscountFixed extends DiscountAbstract
{
    /**
     * Rule Action code
     */
    public const PRODUCTSETDISCOUNT = "product_set_discount_fixed";

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

        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $itemPrice              = $this->validator->getItemPrice($item);
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $productModel           = $this->product->create()->load($item->getProductId());
        $childSpecialPriceFlag  = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        $specialPriceFlag       = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag          = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        
        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }
        // $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds(),$qty);
        // if(!in_array($rule->getRuleId(),$highestRuleIds)) {
            
        //     return $discountData;
        // }

        $discountQty        = $rule->getDiscountQty();
        $maxDiscountAmount  = $rule->getMaxDiscount();
        $discountSteps      = $rule->getDiscountStep();
        list(
            $check,
            $ruleArray,
            $cartItems,
            $count,
            $promotionSKUs
        ) = $this->getRuleNCart($rule, $item, self::PRODUCTSETDISCOUNT);
        
        $discountAmount             = 0;
        $promoSKUsPriceTotal        = 0;
        $allSkusPriceTotal          = 0;
        $totalSkus                  = count($promotionSKUs);

        if (!empty($promotionSKUs) && $check >= $totalSkus) {
            $avgQty             = 0;
            foreach ($ruleArray as $currentRule) {
                $avgQty = $this->getAvgQty($avgQty, $cartItems, $currentRule[0]);
                if ($avgQty >= $discountSteps) {
                    foreach ($cartItems as $key => $items) {
                        $allSkusPriceTotal = $allSkusPriceTotal + $items[0] * $avgQty ;
                        $promoSKUsPriceTotal = $this->getPromoSKUsPriceTotal(
                            $currentRule,
                            $items,
                            $avgQty,
                            $key,
                            $cartItems
                        );
                    }
                    $amountAfterDiscount =  $promoSKUsPriceTotal - $discountAmount;
                    if ($amountAfterDiscount < $currentRule[2]) {
                        $discountAmount += $amountAfterDiscount;
                    } else {
                        $discountAmount += $currentRule[2];
                    }
                }
            }

            if ($discountAmount > 0) {
                if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                    $discountAmount = $maxDiscountAmount;
                }
                $percentageDisocunt =  $discountAmount/$allSkusPriceTotal*100;
                $discountAmount = ($item->getPrice()*$avgQty)*($percentageDisocunt/100);
                $discountData->setAmount($discountAmount);
                $discountData->setBaseAmount($discountAmount);
                $discountData->setOriginalAmount($discountAmount);
                $discountData->setBaseOriginalAmount($discountAmount);
            }
        }
        return $discountData;
    }

    /**
     * Get PromoSKUsPriceTotal
     *
     * @param object $currentRule
     * @param object $items
     * @param int    $avgQty
     * @param int    $key
     * @param array  $cartItems
     * @return integer
     */
    private function getPromoSKUsPriceTotal($currentRule, $items, $avgQty, $key, $cartItems)
    {
        $promoSKUsPriceTotal = 0;
        if ($currentRule[3] == 'PromoCategory') {
            $result = array_intersect($items[1], $currentRule[0]);
            if (count($result) > 0) {
                $promoSKUsPriceTotal = $promoSKUsPriceTotal + $items[0] * $avgQty ;
                $cartItems[$key][2] = $cartItems[$key][2] - $avgQty;
                return $promoSKUsPriceTotal;
            }
        } elseif (in_array($items[1], $currentRule[0])) {
            $promoSKUsPriceTotal = $promoSKUsPriceTotal + $items[0] * $avgQty ;
            $cartItems[$key][2] = $cartItems[$key][2] - $avgQty;
            return $promoSKUsPriceTotal;
        }
    }
}
