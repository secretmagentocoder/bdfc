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

class ProductSetPercent extends DiscountAbstract
{
    /**
     * Rule Action code
     */
    public const PRODUCTSETDISCOUNT = "product_set_percent";
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
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
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

        list(
            $check,
            $ruleArray,
            $cartItems,
            $count,
            $promotionSKUs
        ) = $this->getRuleNCart($rule, $item, self::PRODUCTSETDISCOUNT);

        $discountQty = $rule->getDiscountQty();
        $maxDiscountAmount = $rule->getMaxDiscount();
        $discountSteps = $rule->getDiscountStep();
        $discountAmount = 0;
        $promoSKUsPriceTotal = 0;
        $totalSKUs = count($promotionSKUs);

        if (!empty($promotionSKUs) && $check >= $totalSKUs) {

            $avgQty = 0;
            foreach ($ruleArray as $currentRule) {

                $avgQty = $this->getAvgQty($avgQty, $cartItems, $currentRule[0]);
                if ($avgQty >= $discountSteps && $currentRule[0]) {
                    $promoSKUsPriceTotal = $this->getPromoSKUsPriceTotal($cartItems, $currentRule, $avgQty);
                    $discountAmount = ($promoSKUsPriceTotal * ($currentRule[2]) / 100);
                }
                break;
            }

            if ($discountAmount > 0) {
                if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                    $discountAmount = $maxDiscountAmount;
                }
        
                $totalCartAmount = $item->getQuote()->getSubtotal();
                $discountAmount = $discountAmount / $totalCartAmount * ($item->getPrice() * $qty);
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
     * @param array  $cartItems
     * @param object $currentRule
     * @param int    $avgQty
     * @return integer
     */
    private function getPromoSKUsPriceTotal($cartItems, $currentRule, $avgQty)
    {
        $promoSKUsPriceTotal = 0;
        foreach ($cartItems as $key => $items) {
            if (is_array($items[1])) {
                $result = array_intersect($items[1], $currentRule[0]);
                if (count($result) > 0) {
                    $promoSKUsPriceTotal = $promoSKUsPriceTotal + $items[0] * $avgQty;
                    $cartItems[$key][2] = $cartItems[$key][2] - $avgQty;
                }
            } elseif (in_array($items[1], $currentRule[0])) {
                        $promoSKUsPriceTotal = $promoSKUsPriceTotal + $items[0] * $avgQty;
                        $cartItems[$key][2] = $cartItems[$key][2] - $avgQty;
            }
        }

        return $promoSKUsPriceTotal;
    }
}
