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

class BuyXGetNFixedDiscount extends DiscountAbstract
{
    /**
     * Calculate Discount for BuyXGetNFixedDiscount
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
       
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $discountData           = $this->discountFactory->create();
        $itemPrice              = $this->validator->getItemPrice($item);
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
        
        
         
        
            $cartSKUs               = [];
            $totalCartItems         = 0;
            $maxDiscountAmount      = $rule->getMaxDiscount();
            $discountStep           = $rule->getDiscountStep();
            $currentDiscountAmount  = $rule->getDiscountAmount();
            $qty                    = $item->getQty();
            $discountAmount         = 0;
            $discountAmountCategory = 0;
            $promoSkus              = $rule->getPromoSkus();
            $totalQuantity          = $item->getQuote()->getItemsQty();
            $yProducts              = $rule->getData('wkrulesrule_nqty');
            $productQty             = [];
            $productPrice           = [];
            
            foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
                $totalCartItems++;
                $currentItemSku = $currentItem->getSku();
                if ($currentItem->getProductType()=='bundle') {
                    $currentItemSku = $this->getBundleProductSku($currentItem->getId(), $currentItemSku, $item);
                }
                $productQty[$currentItemSku] = $currentItem->getQty();
                $productPrice[$currentItemSku] = $currentItem->getPrice();
                $cartSKUs[] = $currentItemSku;
            }
            if ($rule->getData('promo_cats') != "") {
                $discountAmountCategory = $this->getAmountCategoryWise(
                    $item,
                    $rule,
                    $cartSKUs,
                    $totalCartItems,
                    $totalQuantity,
                    $yProducts,
                    $productQty,
                    $productPrice
                );
            }
            
            if ($discountAmountCategory == 0) {
                $discountAmount = $this->getAmountSkuWise(
                    $item,
                    $promoSkus,
                    $cartSKUs,
                    $discountStep,
                    $qty,
                    $discountAmount,
                    $currentDiscountAmount,
                    $totalCartItems,
                    $totalQuantity,
                    $yProducts,
                    $productQty,
                    $productPrice
                );
            } else {
                $discountAmount = $discountAmountCategory;
            }
        
        $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
        if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
            $discountAmount=0;
        } 
        if ($discountAmount > 0) {
            if ($maxDiscountAmount > 0 && $maxDiscountAmount < $discountAmount) {
                $discountAmount = $maxDiscountAmount;
            }
            $discountData->setAmount($discountAmount);
            $discountData->setBaseAmount($discountAmount);
            $discountData->setOriginalAmount($discountAmount);
            $discountData->setBaseOriginalAmount($discountAmount);
        }
        return $discountData;
    }

    /**
     * Get discount amount as per promotional Sku
     *
     * @param object $item
     * @param string $promoSkus
     * @param string $cartSKUs
     * @param integer $discountStep
     * @param integer $qty
     * @param integer $discountAmount
     * @param integer $currentDiscountAmount
     * @param integer $totalCartItems
     * @param integer $totalQuantity
     * @param integer $yProducts
     * @param integer $productQty
     * @param integer $productPrice
     * @return integer
     */
    private function getAmountSkuWise(
        $item,
        $promoSkus,
        $cartSKUs,
        $discountStep,
        $qty,
        $discountAmount,
        $currentDiscountAmount,
        $totalCartItems,
        $totalQuantity,
        $yProducts,
        $productQty,
        $productPrice
    ) {
        if (empty($yProducts) || $yProducts ==0) {
            $yProducts = 1;
        }
        $skus       = explode(',', $promoSkus);
        $skuCount   = 0;
        $totalSkus  = 0;
        $currentItemSku = $item->getSku();
        if ($item->getProductType()=='bundle') {
            $currentItemSku = $this->getBundleProductSku($item->getId(), $currentItemSku);
        }
        
        if (in_array($currentItemSku, $cartSKUs) && in_array($currentItemSku, $skus)) {
            foreach ($skus as $sku) {
                $productQtyitem = $productQty[$sku];
                $qty = $totalQuantity - $productQtyitem;
                if (in_array($sku, $cartSKUs) && $discountStep <= $qty && $yProducts <= $productQtyitem) {
                    $skuCount ++;
                    if ($currentDiscountAmount <= $productPrice[$sku]) {
                        $discountAmount = $currentDiscountAmount;
                    }
                }
                $totalSkus ++;
            }
        }
        if ($skuCount != $totalSkus) {
                $discountAmount = 0;
        }
        return $discountAmount;
    }

    /**
     * Category Wise Discount
     *
     * @param mixed $item
     * @param mixed $rule
     * @param mixed $cartSKUs
     * @param mixed $totalCartItems
     * @param mixed $totalQuantity
     * @param mixed $yProducts
     * @param mixed $productQty
     * @param mixed $productPrice
     * @return void
     */
    private function getAmountCategoryWise(
        $item,
        $rule,
        $cartSKUs,
        $totalCartItems,
        $totalQuantity,
        $yProducts,
        $productQty,
        $productPrice
    ) {
        $discountAmount        = 0;
        $categories            = explode(",", $rule->getData('promo_cats'));
        $currentDiscountAmount = $rule->getDiscountAmount();
        $qty                   = $item->getQty();
        $itemPrice             = $this->validator->getItemPrice($item);
        $product               = $this->promotionsHelper->getProduct($item->getProductId());
        $sku                   = $product->getSku();
        $promotionCategory     = $product->getCategoryIds();
        $discountStep          = $rule->getDiscountStep();
        foreach ($categories as $categoryId) {
            if (in_array($categoryId, $promotionCategory) && in_array($sku, $cartSKUs)) {
                $productQtyitem = $productQty[$sku];
                $qty = $totalQuantity - $productQtyitem;
                if ($discountStep <= $qty && $yProducts <= $productQtyitem) {
                    $discountAmount = $currentDiscountAmount;
                    break;
                }
            }
        }
        return $discountAmount;
    }
}
