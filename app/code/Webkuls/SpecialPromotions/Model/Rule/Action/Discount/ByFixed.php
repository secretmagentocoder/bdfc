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

class ByFixed extends \Magento\SalesRule\Model\Rule\Action\Discount\ByFixed
{
    /**
     * @var \Webkuls\SpecialPromotions\Helper\Data
     */
    protected $promotionsHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;
   
    /**
     *
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\Product $product
     * @param \Webkuls\SpecialPromotions\Helper\Data $promotionsHelper
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $product,
        \Webkuls\SpecialPromotions\Helper\Data $promotionsHelper,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->product = $product;
        $this->cart = $cart;
        $this->promotionsHelper = $promotionsHelper;
        $this->validator = $validator;
        $this->discountFactory = $discountDataFactory;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    /**
     * Calculate the dicount amount
     *
     * @param Object $rule
     * @param object $item
     * @param integer $qty
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

        $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $item->getQuote()->getStore());
        
        $discountData->setAmount($qty * $quoteAmount);
        $discountData->setBaseAmount($qty * $rule->getDiscountAmount());
        
        return $discountData;
    }

    /**
     * @param float $qty
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return float
     */
    public function fixQuantity($qty, $rule)
    {
        $step = $rule->getDiscountStep();
        if ($step) {
            $qty = floor($qty / $step) * $step;
        }
        return $qty;
    }
}
