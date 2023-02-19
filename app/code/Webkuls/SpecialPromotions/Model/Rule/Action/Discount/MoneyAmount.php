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

class MoneyAmount extends DiscountAbstract
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

        foreach ($this->cart->getQuote()->getAllVisibleItems() as $currentItem) {
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
            $discountAmount = $discountAmount/$proCount;
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }
}
