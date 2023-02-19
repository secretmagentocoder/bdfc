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
namespace Webkul\SpecialPromotions\Plugin\Model\Rule\Action\Discount;

class ByFixed
{
    /**
     * @var \Webkul\SpecialPromotions\Helper\Data
     *
     * */
    protected $promotionsHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     *
     * */
    protected $product;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory
     *
     * */
    protected $discountDataFactory;
   
    /**
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Webkul\SpecialPromotions\Helper\Data $promotionsHelper
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $product,
        \Webkul\SpecialPromotions\Helper\Data $promotionsHelper,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory
    ) {
        $this->product = $product;
        $this->promotionsHelper = $promotionsHelper;
        $this->discountFactory = $discountDataFactory;
    }

     /**
      * Calculate Discount plugin
      *
      * @param \Magento\SalesRule\Model\Rule\Action\Discount\ByFixed $subject
      * @param \Closure $proceed
      * @param arrayObject $rule
      * @param arrayObject $item
      * @param int $qty
      * @return \Closure $proceed
      */
    public function aroundCalculate(
        \Magento\SalesRule\Model\Rule\Action\Discount\ByFixed $subject,
        \Closure $proceed,
        $rule,
        $item,
        $qty
    ) {
        $discountData           = $this->discountFactory->create();
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
        return $proceed($rule, $item, $qty);
    }
}
