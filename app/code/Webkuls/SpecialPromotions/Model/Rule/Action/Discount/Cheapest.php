<?php
/**
 * Webkuls Software.
 *
 * @category   Webkuls
 * @package    Webkuls_SpecialPromotions
 * @author     Webkuls Software Private Limited
 * @copyright  Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license    https://store.Webkuls.com/license.html
 */
namespace Webkuls\SpecialPromotions\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule\Action\Discount;

class Cheapest extends DiscountAbstract
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
        $currentItemId          = 0;
        $currentItemPrice       = $itemPrice;
        $proCount               = 0;
        $discountStep           = $rule->getDiscountStep();
        $discountAmount         = $rule->getDiscountAmount();
        $maxDiscountAmount      = $rule->getMaxDiscount();
                
//        foreach ($this->cart->getQuote()->getAllVisibleItems() as $currentItem) {
//            if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
//                continue;
//            }
//            if ($currentItemPrice >= $currentItem->getPrice()) {
//                $currentItemPrice    = $currentItem->getPrice();
//                $currentItemId       = $currentItem->getId();
//            }
//            $proCount++;
//        }
        
        if ($currentItemId != $item->getId()) {
            return $discountData;
        }
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
        if ($proCount > $discountStep) {
            if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                $discountAmount = $maxDiscountAmount;
            }
            $discountAmount = $itemPrice * ($discountAmount / 100) ;
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }
}
