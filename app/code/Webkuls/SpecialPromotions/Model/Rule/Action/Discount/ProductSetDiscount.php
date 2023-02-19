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

class ProductSetDiscount extends DiscountAbstract
{
    /**
     * Rule Action code
     */
    const PRODUCTSETDISCOUNT = "product_set_percent";
    
    /**
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
        $discountQty            = $rule->getDiscountQty();
        $maxDiscountAmount      = $rule->getMaxDiscount();
        list(
            $check,
            $ruleArray,
            $cartItems,
            $count,
            $promotionSKUs
        ) = $this->getRuleNCart($rule, $item, self::PRODUCTSETDISCOUNT);
        
        $discountAmount             = 0;
        $promoSKUsPriceTotal        = 0;
        
        if (!empty($promotionSKUs) && $check >= count($promotionSKUs)) {
            $avgQty             = 0;
            foreach ($ruleArray as $currentRule) {
                $avgQty = $this->getAvgQty($avgQty, $cartItems, $currentRule[0]);
                if ($avgQty) {
                    foreach ($cartItems as $key => $items) {
                        if (in_array($items[1], $currentRule[0])) {
                            $promoSKUsPriceTotal = $promoSKUsPriceTotal + $items[0] * $avgQty ;
                            $cartItems[$key][2] = $cartItems[$key][2] - $avgQty;
                        }
                    }
                    $discountAmount = ($promoSKUsPriceTotal *($currentRule[2]) / 100);
                }
                break;
            }
            if ($discountAmount > 0) {
                if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                    $discountAmount = $maxDiscountAmount;
                }
                $discountAmount = $discountAmount / $count;
                $discountData->setAmount($discountAmount);
                $discountData->setBaseAmount($discountAmount);
                $discountData->setOriginalAmount($discountAmount);
                $discountData->setBaseOriginalAmount($discountAmount);
            }
        }
        return $discountData;
    }
}
