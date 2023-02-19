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

use Magento\SalesRule\Model\DeltaPriceRound;

class CartFixed extends \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed
{
    /**
     * @var \Webkuls\SpecialPromotions\Helper\Data
     *
     * */
    protected $promotionsHelper;

    /**
     * @var\Magento\Catalog\Model\Product
     *
     * */
    protected $product;
     
    /**
     * @param \Webkuls\MpSpecialPromotions\Helper      $promotionsHelper
     * */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $product,
        \Webkuls\SpecialPromotions\Helper\Data $promotionsHelper,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        DeltaPriceRound $deltaPriceRound
    ) {
        $this->product = $product;
        $this->cart = $cart;
        $this->promotionsHelper = $promotionsHelper;
        $this->validator = $validator;
        $this->discountFactory = $discountDataFactory;
        $this->priceCurrency = $priceCurrency;
        $this->deltaPriceRound = $deltaPriceRound;
        parent::__construct($validator, $discountDataFactory, $priceCurrency, $deltaPriceRound);
    }

    /**
     * Store information about addresses which cart fixed rule applied for
     *
     * @var int[]
     */
    protected $_cartFixedRuleUsedForAddress = [];

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

        $ruleTotals = $this->validator->getRuleItemTotalsInfo($rule->getId());

        $quote = $item->getQuote();
        $address = $item->getAddress();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        /**
         * prevent applying whole cart discount for every shipping order, but only for first order
         */
        if ($quote->getIsMultiShipping()) {
            $usedForAddressId = $this->getCartFixedRuleUsedForAddress($rule->getId());
            if ($usedForAddressId && $usedForAddressId != $address->getId()) {
                return $discountData;
            } else {
                $this->setCartFixedRuleUsedForAddress($rule->getId(), $address->getId());
            }
        }
        $cartRules = $address->getCartFixedRules();
        if (!isset($cartRules[$rule->getId()])) {
            $cartRules[$rule->getId()] = $rule->getDiscountAmount();
        }

        if ($cartRules[$rule->getId()] > 0) {
            $store = $quote->getStore();
            if ($ruleTotals['items_count'] <= 1) {
                $quoteAmount = $this->priceCurrency->convert($cartRules[$rule->getId()], $store);
                $baseDiscountAmount = min($baseItemPrice * $qty, $cartRules[$rule->getId()]);
            } else {
                $discountRate = $baseItemPrice * $qty / $ruleTotals['base_items_price'];
                $maximumItemDiscount = $rule->getDiscountAmount() * $discountRate;
                $quoteAmount = $this->priceCurrency->convert($maximumItemDiscount, $store);

                $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
                $this->validator->decrementRuleItemTotalsCount($rule->getId());
            }

            $baseDiscountAmount = $this->priceCurrency->round($baseDiscountAmount);

            $cartRules[$rule->getId()] -= $baseDiscountAmount;

            $discountData->setAmount($this->priceCurrency->round(min($itemPrice * $qty, $quoteAmount)));
            $discountData->setBaseAmount($baseDiscountAmount);
            $discountData->setOriginalAmount(min($itemOriginalPrice * $qty, $quoteAmount));
            $discountData->setBaseOriginalAmount($this->priceCurrency->round($baseItemOriginalPrice));
        }
        $address->setCartFixedRules($cartRules);
        
        return $discountData;
    }

    /**
     * Set information about usage cart fixed rule by quote address
     *
     * @param int $ruleId
     * @param int $itemId
     * @return void
     */
    protected function setCartFixedRuleUsedForAddress($ruleId, $itemId)
    {
        $this->_cartFixedRuleUsedForAddress[$ruleId] = $itemId;
    }

    /**
     * Retrieve information about usage cart fixed rule by quote address
     *
     * @param int $ruleId
     * @return int|null
     */
    protected function getCartFixedRuleUsedForAddress($ruleId)
    {
        if (isset($this->_cartFixedRuleUsedForAddress[$ruleId])) {
            return $this->_cartFixedRuleUsedForAddress[$ruleId];
        }
        return null;
    }
}
