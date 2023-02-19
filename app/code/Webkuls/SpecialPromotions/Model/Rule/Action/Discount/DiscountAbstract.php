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

abstract class DiscountAbstract extends \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
{
    /**
     * @var \Webkuls\SpecialPromotions\Helper\Data
     *
     * */
    protected $promotionsHelper;

    /**
     * @var product
     */
    protected $product;

     /**
      * @var $rule
      */
    protected $rule;

    /**
     *
     * @var product entity_id
     */
    protected $currentProductId;

    /**
     *
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Webkuls\SpecialPromotions\Helper\Data $promotionsHelper
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\SalesRule\Model\Validator $validator,
        \Webkuls\SpecialPromotions\Helper\Data $promotionsHelper,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->rule             = $rule;
        $this->cart             = $cart;
        $this->product          = $product;
        $this->validator        = $validator;
        $this->promotionsHelper = $promotionsHelper;
        $this->discountFactory  = $discountDataFactory;
        $this->priceCurrency    = $priceCurrency;
    }

    /**
     * This function check the promotional category with product category
     *
     * @param array $itemCategory
     * @param array $promoCategories
     * @return boolean
     */
    private function isCategoryIdMatched($itemCategory, $promoCategories)
    {
        $flag = false;
        foreach ($itemCategory as $categoryId) {
            if (in_array($categoryId, $itemCategory)) {
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * This function will return the Applicable sku for the rule
     *
     * @param Object $rule
     * @return Array
     */
    private function getApplicableSKUS($rule)
    {
        $promoCategories  = $rule->getPromoCats();
        $promotionSKUs    = explode(",", $rule->getPromoSkus());
        if ($promoCategories) {
            foreach ($this->cart->getItems() as $currentItem) {
                $itemSku      = $currentItem->getProduct()->getSku();
                $itemCategory = $currentItem->getProduct()->getCategoryIds();
                $isContainId  = $this->isCategoryIdMatched($itemCategory, $promoCategories);
                if ($isContainId) {
                    if (!in_array($itemSku, $promotionSKUs)) {
                        $promotionSKUs[] = $itemSku;
                    }
                }
            }
        }
        return $promotionSKUs;
    }
    
    /**
     * This function returns the average Quantity of the product in cart which are applicable for rule
     *
     * @param integer $avgQty
     * @param array $cartItems
     * @param array $currentRule
     * @return integer
     */
    public function getAvgQty($avgQty, $cartItems, $currentRule)
    {
        $avgQty   = 0;
        $matchSku = 0;
        foreach ($cartItems as $cartItem) {
            if (in_array($cartItem[1], $currentRule)) {
                $matchSku ++;
                if ($avgQty == 0 && $cartItem[2] > 0) {
                    $avgQty = $cartItem[2];
                } elseif ($avgQty > $cartItem[2] && $cartItem[2] > 0) {
                    $avgQty = $cartItem[2];
                }
            }
        }
        if (is_array($currentRule) && count($currentRule) > $matchSku) {
            return 0;
        }
        return $avgQty;
    }

    /**
     * this function will return the rules and cartItems as per promotional skus
     *
     * @param object $rule
     * @param object $item
     * @return array
     */
    protected function getRuleNCart($rule, $item, $productSet)
    {
        $ruleArray      = $cartItems = [];
        $promotionSKUs  = $this->getApplicableSKUS($rule);
        $count          = $check = 0;
        $allRules       = $this->rule->getCollection()
                          ->addFieldToFilter('simple_action', $productSet)
                          ->setOrder('sort_order', 'ASC');
        foreach ($allRules as $rule) {
            if ($rule->getPromoSkus() || $rule->getPromoCats()) {
                $ruleArray[$rule->getId()][] =  explode(',', $rule->getPromoSkus());
                $ruleArray[$rule->getId()][] =  $rule->getSortOrder();
                $ruleArray[$rule->getId()][] =  $rule->getDiscountAmount();
            }
        }
        foreach ($this->cart->getQuote()->getAllVisibleItems() as $currentItem) {
            if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
                continue;
            }
                $count ++;
                $cartItems[$currentItem->getId()][] = $currentItem->getPrice();
                $cartItems[$currentItem->getId()][] = $currentItem->getSku();
                $cartItems[$currentItem->getId()][] = $currentItem->getQty();
            if (in_array($currentItem->getSku(), $promotionSKUs)) {
                $check ++;
            }
        }
        return [ $check, $ruleArray, $cartItems, $count, $promotionSKUs ];
    }

    /**
     * this function loops every items and calls the validate amount function
     *
     * @param array $items
     * @param integer $groupAmount
     * @param integer $toDiscountItems
     * @return array
     */
    protected function getNGroupPrice($items, $groupAmount, $toDiscountItems, $flag = 0)
    {
        $groupPrice = $count = 0;
        $itemsCount = count($items);
        for ($i=1; $i<= $itemsCount; $i++) {
            list($groupPrice, $count) = $this->checkAmountValid($i, $toDiscountItems, $items, $groupAmount, $flag);
            if ($groupPrice != 0) {
                break;
            }
        }
        return [$groupPrice, $count];
    }

    /**
     * This function is used to validate the group price for number of items in group
     *
     * @param integer $i
     * @param integer $toDiscountItems
     * @param array $items
     * @param ingeter $groupAmount
     * @return array
     */
    protected function checkAmountValid($i, $toDiscountItems, $items, $groupAmount, $flag)
    {
        $countItems = $groupPrice = 0;
        $countToDiscountItems = $productCount = 0;
        foreach ($items as $key => $value) {
            $countToDiscountItems ++;
            if ($countToDiscountItems >= $i) {
                $countItems ++;
                if ($countItems <= $toDiscountItems) {
                    $groupPrice = $groupPrice + $value['price'];
                    if ($this->currentProductId == $value["product_id"]) {
                        $productCount ++;
                    }
                }
            }
        }
        if ($flag && ($groupPrice <= ($groupPrice * ($groupAmount / 100)))) {
            return [0,$productCount];
        } elseif ($groupPrice <= $groupAmount) {
            return [0,$productCount];
        } else {
            return [$groupPrice, $productCount];
        }
    }

    /**
     * get discount amount as per promotional category
     *
     * @param object $item
     * @param object $rule
     * @return integer
     */
    public function getItemCategoryWise($item, $rule, $fixed = 0)
    {
        $discountAmount = 0;
        $categories     = explode(",", $rule->getData('promo_cats'));
        $product        = $this->promotionsHelper->getProduct($item->getProductId());
        $promotionCategory        = $product->getCategoryIds();
        $discountStep              = $rule->getDiscountQty();
        $discountAmount              = $rule->getDiscountAmount();
        $qty            = $item->getQty();
        $itemPrice      = $this->validator->getItemPrice($item);
        $count          = 0;
        foreach ($categories as $categoryId) {
            $count ++;
            if (in_array($categoryId, $promotionCategory)) {
                if ($discountStep >= $count) {
                    if ($fixed) {
                        $discountAmount = $discountAmount + $fixed;
                    } else {
                        $discountAmount = $discountAmount + $itemPrice;
                    }
                    break;
                }
            }
        }
        return $discountAmount;
    }

    /**
     * It divides the discount as per promo category or sku
     *
     * @param integer $categoryDiscount
     * @param integer $discountAmount
     * @param array $cartSKUs
     * @param array $skus
     * @return integer
     */
    protected function divideDiscount($categoryDiscount, $discountAmount, $cartSKUs, $skus)
    {
        if ($categoryDiscount) {
            $discountAmount = (($discountAmount) / count($cartSKUs));
        } else {
            $discountAmount = (($discountAmount) / count($skus));
        }
        return $discountAmount;
    }

    /**
     * Product entity_id
     *
     * @param string $sku
     * @return integer
     */
    protected function getProductIdBySku($sku)
    {
        return $this->product->create()->getIdBySku($sku);
    }

     /**
     * Bundle product SKu
     *
     * @param cartObject $itemId
     * @param string $currentItemSku
     * @return string
     */
    public function getBundleProductSku($itemId,$currentItemSku) {
        foreach ($this->cart->getQuote()->getAllItems() as $currentItem) {
            if ($currentItem->getParentItemId()==$itemId) {
                $currentItemSku = str_replace("-".$currentItem->getSku(), "", $currentItemSku);
            }
        }
        return $currentItemSku;
    }
}
