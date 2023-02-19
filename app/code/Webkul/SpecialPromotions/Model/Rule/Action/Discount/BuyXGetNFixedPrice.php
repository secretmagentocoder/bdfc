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
use \Webkul\SpecialPromotions\Logger\Logger;

class BuyXGetNFixedPrice extends DiscountAbstract
{
/**
 * Initialize
 *
 * @param \Webkul\SpecialPromotions\Logger\Logger $logger
 */
    public function __construct(
        \Webkul\SpecialPromotions\Logger\Logger $logger
    ) {
        $this->logger = $logger;
    }
    /**
     * Calculate Discount for BuyXGetNFixedPrice
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
        $productModel = $this->product->create()->load($item->getProductId());
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
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
        
        
        $discountStep = $rule->getDiscountStep();
        $currentDiscountAmount = $rule->getDiscountAmount();
        $qty = $item->getQty();
        $discountAmount = 0;
        $cartSKUs = [];
        $discountAmountCategory = 0;
        $prodCount = 0;
        $maxDiscountAmount = $rule->getMaxDiscount();
        $totalQuantity = $item->getQuote()->getItemsQty();
        $yProducts = $rule->getData('wkrulesrule_nqty');
        $productQty = [];

        foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
            $prodCount++;
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
                $totalQuantity,
                $yProducts,
                $productQty
            );
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
                $prodCount,
                $totalQuantity,
                $yProducts,
                $productQty
            );
        } else {
            $discountAmount = $discountAmountCategory;
        }
        if ($maxDiscountAmount > 0 && $maxDiscountAmount <= $discountAmount) {
            $discountAmount = $maxDiscountAmount;
        }
        $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
        if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
            $discountAmount=0;
        } 
        if ($discountAmount > 0) {
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
   * @param [type] $totalQuantity
   * @param [type] $yProducts
   * @param [type] $productQty
   * @return void
   */
    private function getAmountCategoryWise($item, $rule, $cartSKUs, $totalQuantity, $yProducts, $productQty)
    {
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
            $this->logger->info('cartsku: ' . json_encode($cartSKUs));
            $this->logger->info('sku: ' . $sku);
            if (in_array($categoryId, $promotionCategory) && in_array($sku, $cartSKUs)) {

                $this->logger->info('inside if:133');
                $productQtyitem = $productQty[$sku];
                $qty = $totalQuantity - $productQtyitem;
                if ($discountStep <= $qty && $yProducts <= $productQtyitem) {
                    $discountAmount = $itemPrice - $currentDiscountAmount;
                    break;
                }
            }
        }

        return $discountAmount;
    }

    /**
     * Get Discount Amount By Sku
     *
     * @param [type] $item
     * @param [type] $cartSKUs
     * @param [type] $rule
     * @param [type] $discountStep
     * @param [type] $qty
     * @param [type] $discountAmount
     * @param [type] $currentDiscountAmount
     * @param [type] $prodCount
     * @param [type] $totalQuantity
     * @param [type] $yProducts
     * @param [type] $productQty
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
        $prodCount,
        $totalQuantity,
        $yProducts,
        $productQty
    ) {
        if (empty($yProducts) || $yProducts == 0) {
            $yProducts = 1;
        }
        $currentItemSku = $item->getSku();
        if ($item->getProductType() == 'bundle') {
            $currentItemSku = $this->getBundleProductSku($item->getId(), $currentItemSku);
        }
        $skus = explode(',', $rule->getData('promo_skus'));
        $skuCount = 0;
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
                        $promoSKUPrice = $product->getPrice();
                    }
                    $qty = $totalQuantity - $productQtyitem;
                    if (in_array($sku, $cartSKUs) && $discountStep <= $qty && $yProducts <= $productQtyitem) {
                        if ($currentItemSku == $sku) {
                            $nDiscountQty = min($qty, $productQtyitem);
                            $discountAmount = $promoSKUPrice - $currentDiscountAmount;
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
}
