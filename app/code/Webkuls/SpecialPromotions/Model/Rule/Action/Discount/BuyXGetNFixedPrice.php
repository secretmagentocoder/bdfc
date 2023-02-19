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

class BuyXGetNFixedPrice extends DiscountAbstract
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
        $discountData = $this->discountFactory->create();
        
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
        $currentDiscountAmount  = $rule->getDiscountAmount();
        $qty                    = $item->getQty();
        $discountAmount         = 0;
        $cartSKUs               = [];
        $discountAmountCategory = 0;
        $prodCount              = 0;
        $maxDiscountAmount      = $rule->getMaxDiscount();
        
//        foreach ($this->cart->getQuote()->getAllVisibleItems() as $currentItem) {
//            if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
//                continue;
//            }
//            $prodCount++;
//            $currentItemSku = $currentItem->getSku();
//            if ($currentItem->getProductType()=='bundle') {
//                $currentItemSku = $this->getBundleProductSku($currentItem->getId(),$currentItemSku);
//            }
//            $cartSKUs[] = $currentItemSku;
//        }
        if ($rule->getData('promo_cats') != "") {
            $discountAmountCategory = $this->getAmountCategoryWise($item, $rule);
        }
        if ($discountAmountCategory == 0) {
            $discountAmount = $this->getAmountSkuWise(
                $item,
                $cartSKUs,
                $rule,
                $discountStep,
                $qty,
                $discountAmount,
                $currentDiscountAmount,
                $prodCount
            );
        } else {
            $discountAmount = $discountAmountCategory;
        }
       
        if ($maxDiscountAmount > 0 && $maxDiscountAmount <= $discountAmount) {
            $discountAmount = $maxDiscountAmount;
        }
        if ($discountAmount > 0) {
            $discountAmount = $discountAmount / $prodCount;
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }

    /**
     * get discount amount as per promotional category
     *
     * @param object $item
     * @param object $rule
     * @return integer
     */
    private function getAmountCategoryWise($item, $rule)
    {
        $discountAmount         = 0;
        $categories             = explode(",", $rule->getData('promo_cats'));
        $product                = $this->promotionsHelper->getProduct($item->getProductId());
        $promotionCategory      = $product->getCategoryIds();
        $discountStep           = $rule->getDiscountStep();
        $currentDiscountAmount  = $rule->getDiscountAmount();
        $qty                    = $item->getQty();
        $itemPrice              = $this->validator->getItemPrice($item);
        foreach ($categories as $categoryId) {
            if (in_array($categoryId, $promotionCategory)) {
                if ($discountStep <= $qty) {
                    $discountAmount = $currentDiscountAmount ;
                    break;
                }
            }
        }
        return $discountAmount;
    }

    /**
     * Get Discount Amount By Sku
     *
     * @return void
     */
    public function getAmountSkuWise(
        $item,
        $cartSKUs,
        $rule,
        $discountStep,
        $qty,
        $discountAmount,
        $currentDiscountAmount,
        $prodCount
    ) {
        if ($prodCount > $discountStep) {
            $currentItemSku = $item->getSku();
            if ($item->getProductType()=='bundle') {
                $currentItemSku = $this->getBundleProductSku($item->getId(),$currentItemSku);
            }
            if (in_array($currentItemSku, $cartSKUs)) {
                $skus = explode(',', $rule->getData('promo_skus'));
                $skuCount = 0;
                foreach ($skus as $sku) {
                    if (in_array($sku, $cartSKUs)) {
                        $product = $this->promotionsHelper->getProduct(
                            $this->product->create()->getIdBySku($sku)
                        );
                        if ($product->getTypeId()=='bundle') {
                            $promoSKUPrice   = $this->getBundleProductPrice($sku);
                        } else {
                            $promoSKUPrice   = $product->getPrice();
                        }
                        if ($discountStep <= $qty) {
                            $skuCount ++;
                            $discountAmount = $discountAmount + ($promoSKUPrice - $currentDiscountAmount);
                        }
                    }
                }
                
                if ($skuCount != count($skus)) {
                    $discountAmount = 0;
                }
            }
        }
        return $discountAmount;
    }

    /**
     * Bundle Product Price
     *
     * @param string $sku
     * @return float
     */
    public function getBundleProductPrice($sku) {
        $price = 0;
        foreach ($this->cart->getQuote()->getAllItems() as $currentItem) {
            if (
                $currentItem->getProductType()=='bundle' 
                && 
                $currentItem->getProduct()->getData('sku') == $sku 
                ) {
                $price = $currentItem->getPrice();
            }
        }
        return $price;
    }
}
