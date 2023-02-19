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


class BuyXGetNPercentDiscount extends DiscountAbstract
{
    
    /**
     * Calculate Discount for BuyXGetNPercentDiscount
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        
        
        $productPrice = 0;
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
        $productModel = $this->product->create()->load($item->getProductId());
        $tierPriceFlag = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        $childSpecialPriceFlag = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        $specialPriceFlag = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);

        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }
        // $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds(),$qty);
        // if(!in_array($rule->getRuleId(),$highestRuleIds)) {
            
        //     return $discountData;
        // }
        // if (!$this->promotionsHelper->checkPriorityRule($rule->getSortOrder())) {
        //     return $discountData;
        // }
        // $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
        // if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
        //     return $discountData;
        // }
        $discountStep = $rule->getDiscountStep();
        $currentDiscountAmount = $rule->getDiscountAmount();
       
        

            $discountAmount = 0;
            $discountAmountCategory = 0;
            $cartSKUs = [];
            $totalCartItems = 0;
            $maxDiscountAmount = $rule->getMaxDiscount();
            $promoSkus = $rule->getpromoSkus();
            $totalQuantity = $item->getQuote()->getItemsQty();
            $yProducts = $rule->getData('wkrulesrule_nqty');
            $productQty = [];
            foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
                $totalCartItems++;
                $currentItemSku = $currentItem->getSku();
                if ($currentItem->getProductType() == 'bundle') {
                    $currentItemSku = $this->getBundleProductSku($currentItem->getId(), $currentItemSku, $item);
                }
                $productQty[$currentItemSku] = $currentItem->getQty();
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
                    $discountAmount,
                    $currentDiscountAmount,
                    $qty,
                    $discountStep,
                    $totalCartItems,
                    $totalQuantity,
                    $yProducts,
                    $productQty,
                    $item,
                    $cartSKUs,
                    $promoSkus
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
     * Get discount amount as per promotional category
     *
     * @param [type] $item
     * @param [type] $rule
     * @param [type] $cartSKUs
     * @param [type] $totalCartItems
     * @param [type] $totalQuantity
     * @param [type] $yProducts
     * @param [type] $productQty
     * @param [type] $productPrice
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
        $discountAmount = 0;
        $categories = explode(",", $rule->getData('promo_cats'));
        $product = $this->promotionsHelper->getProduct($item->getProductId());
        $sku = $product->getSku();
        $promotionCategory = $product->getCategoryIds();
        $discountStep = $rule->getDiscountStep();
        $currentDiscountAmount = $rule->getDiscountAmount();
        $qty = $item->getQty();
        $itemPrice = $this->validator->getItemPrice($item);
        foreach ($categories as $categoryId) {
            if (in_array($categoryId, $promotionCategory) && in_array($sku, $cartSKUs)) {
                $productQtyitem = $productQty[$sku];
                $qty = $totalQuantity - $productQtyitem;
                if ($discountStep <= $qty && $yProducts <= $productQtyitem) {
                    $nDiscountQty = min($qty, $productQtyitem);
                    $discountAmount = $discountAmount + ($itemPrice * $currentDiscountAmount / 100) * $nDiscountQty;
                    break;
                }
            }
        }
        return $discountAmount;
    }

    /**
     * Get discount amount as per promotional category
     *
     * @param integer   $discountAmount
     * @param integer   $currentDiscountAmount
     * @param integer   $qty
     * @param integer   $discountStep
     * @param integer   $totalCartItems
     * @param integer   $totalQuantity
     * @param mixed     $yProducts
     * @param integer   $productQty
     * @param object    $item
     * @param string    $cartSKUs
     * @param string    $promoSkus
     * @return integer
     */
    private function getAmountSkuWise(
        $discountAmount,
        $currentDiscountAmount,
        $qty,
        $discountStep,
        $totalCartItems,
        $totalQuantity,
        $yProducts,
        $productQty,
        $item,
        $cartSKUs,
        $promoSkus
    ) {
        if (empty($yProducts) || $yProducts == 0) {
            $yProducts = 1;
        }

        $currentItemSku = $item->getSku();

        if ($item->getProductType() == 'bundle') {
            $currentItemSku = $this->getBundleProductSku($item->getId(), $currentItemSku);
        }
        $skus = explode(',', $promoSkus);
        if (in_array($currentItemSku, $cartSKUs) && in_array($currentItemSku, $skus)) {
            foreach ($skus as $sku) {
                if (array_key_exists($sku, $productQty)) {
                    $product = $this->promotionsHelper->getProduct(
                        $this->product->create()->getIdBySku($sku)
                    );
                    $productQtyitem = $productQty[$sku];
                    if ($product->getTypeId() == 'bundle') {
                        $promoSKUPrice = $this->getBundleProductPrice($sku);
                    } else {
                        $check = $this->product->create()->getIdBySku($sku);
                        $promoSKUPrice = $check ? $product->getPrice() : $this->getCustomOptionProductPrice($sku);
                    }
                    $qty = $totalQuantity - $productQtyitem;
                    if (in_array($sku, $cartSKUs) && $discountStep <= $qty && $yProducts <= $productQtyitem) {
                        if ($currentItemSku == $sku) {
                            $nDiscountQty = min($qty, $productQtyitem);
                            $discountAmount = $discountAmount +
                            ($promoSKUPrice * $currentDiscountAmount / 100) * $nDiscountQty;
                        }
                    }
                }
            }
        }

        return $discountAmount;
    }

    /**
     * Bundle Product Price
     *
     * @param string $sku
     * @param array $item
     * @return int/float
     */
    public function getBundleProductPrice($sku, $item)
    {
        $price = 0;
        foreach ($item->getQuote()->getAllItems() as $currentItem) {
            if ($currentItem->getProductType() == 'bundle'
                &&
                $currentItem->getProduct()->getData('sku') == $sku
            ) {
                $price = $currentItem->getPrice();
            }
        }
        return $price;
    }

    /**
     * Custom Option Product Price
     *
     * @param string $sku
     * @return int/float
     */
    public function getCustomOptionProductPrice($sku)
    {
        $totalSkuPrice = 0;
        $skuArray = explode('-', $sku);
        $updatedSku = $skuArray[0];

        $product = $this->promotionsHelper->getProduct(
            $this->product->create()->getIdBySku($updatedSku)
        );

        $checkOptions = $product->has_options();
        if ($checkOptions) {
            $optionsArray = $product->getOptions();
            foreach ($optionsArray as $option) {
                if ($sku == $updatedSku.'-'.$option->getSku()) {
                    $totalSkuPrice = $product->getPrice() + $option->getPrice();
                }
            }
        }
        return $totalSkuPrice;
    }
    /**
     * To check the Priority applied 
     *
     * @param string $sortOrder
     * @return boolean
     */
    public function checkPriority($sortOrder)
    {
        if ($sortOrder=='1' || $sortOrder == 1) {
            return true;
        }
        return false;
    }
}

