<?php
namespace Ecommage\SalesRule\Plugin\Model\Rule\Action\Discount;

use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Ecommage\SalesRule\Plugin\Model\Rule\AbstractRule;

/**
 * Class GroupNth
 */
class GroupNth extends AbstractRule
{
    /**
     * @var DataFactory
     */
    protected $discountFactory;
    /**
     * @var array
     */
    protected $discounts = [];
    /**
     * @var \Webkul\SpecialPromotions\Helper\Data
     */
    protected $promotionsHelper;

    /**
     * GroupNth constructor.
     *
     * @param \Webkul\SpecialPromotions\Helper\Data $promotionsHelper
     * @param DataFactory                           $discountDataFactory
     * @param Registry                              $registry
     */
    public function __construct(
        \Webkul\SpecialPromotions\Helper\Data $promotionsHelper,
        DataFactory $discountDataFactory,
        Registry $registry
    ) {
        $this->promotionsHelper = $promotionsHelper;
        $this->discountFactory  = $discountDataFactory;
        parent::__construct($registry);
    }

    /**
     * @param          $subject
     * @param callable $proceed
     * @param          $rule
     * @param          $item
     * @param          $qty
     *
     * @return Data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCalculate($subject, callable $proceed, $rule, $item, $qty)
    {
        $discount = 0;
        $skus     = $this->getSkus();
        //$discountData = $proceed($rule, $item, $qty);
        /** @var Data $discountData */
        $discountData          = $this->discountFactory->create();
        $productModel          = $this->loadProduct($item->getProductId());
        $childSpecialPriceFlag = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        $specialPriceFlag      = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag         = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }

        if (!empty($skus)) {
            $items = $item->getQuote()->getItems();
            $this->processItems($rule, $items, $skus);
            $discount = $this->getDiscountRuleItem($rule, $item);
        }

        $discountData->setAmount($discount);
        $discountData->setBaseAmount($discount);
        $discountData->setOriginalAmount($discount);
        $discountData->setBaseOriginalAmount($discount);
        return $discountData;
    }

    /**
     * @param $id
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function loadProduct($id)
    {
        return $this->promotionsHelper->getProduct($id);
    }

    /**
     * @return array
     */
    protected function getSkus(): array
    {
        $skus = $this->registry->registry(self::SKU_IS_ONE_OF);
        if (!empty($skus) && is_string($skus)) {
            $skus = explode(',', $skus);
        }

        return (array)$skus;
    }

    /**
     * @param $firstItem
     * @param $secondItem
     *
     * @return bool
     */
    protected function sortQuoteItems($firstItem, $secondItem)
    {
        if ((float)$firstItem->getPrice() > (float)$secondItem->getPrice()) {
            return false;
        }

        return true;
    }

    /**
     * @param $rule
     * @param $item
     *
     * @return int
     */
    protected function getDiscountRuleItem($rule, $item)
    {
        if (!isset($this->discounts[$rule->getId()])) {
            return 0;
        }

        return $this->discounts[$rule->getId()][$item->getItemId()] ?? 0;
    }

    /**
     * @param $rule
     * @param $items
     * @param $skus
     *
     * @return $this
     */
    protected function processItems($rule, $items, $skus)
    {
        if (count($items)) {
            $totalQty      = 0;
            $discountItems = [];
            /** @var Item $item */
            foreach ($items as $item) {
                if (in_array($item->getSku(), $skus)) {
                    $discountItems[] = $item;
                    $totalQty        += (float)$item->getQty();
                }
            }
            //reorder items by descending price
            usort($discountItems, [$this, 'sortQuoteItems']);
            $discountStep   = (float)$rule->getDiscountStep();
            $discountAmount = (float)$rule->getDiscountAmount();
            $maxDiscount    = (float)$rule->getMaxDiscount();
            if ($discountAmount && $totalQty >= $discountStep) {
                $discount             = 0;
                $totalItemQtyDiscount = floor($totalQty / $discountStep) * $discountStep;
                /** @var Item $discountItem */
                foreach ($discountItems as $discountItem) {
                    $amount = 0;
                    if ($totalItemQtyDiscount > 0 && $totalItemQtyDiscount > $discountItem->getQty()) {
                        $amount               = $discountItem->getQty() * $discountAmount;
                        $totalItemQtyDiscount -= $discountItem->getQty();
                    } elseif ($totalItemQtyDiscount > 0 && $discountItem->getQty() == $totalItemQtyDiscount) {
                        $amount               = $discountItem->getQty() * $discountAmount;
                        $totalItemQtyDiscount = 0;
                    } elseif ($totalItemQtyDiscount > 0 && $discountItem->getQty() > $totalItemQtyDiscount) {
                        $diffQty              = $discountItem->getQty() - $totalItemQtyDiscount;
                        $amount               = $diffQty * $discountAmount;
                        $totalItemQtyDiscount -= $diffQty;
                    }
                    //check if the total discount amount is exceeded or not
                    //if yes, only take discount up to the allowed limit
                    $fdiscount = $discount + $amount;
                    if ($maxDiscount && $fdiscount > $maxDiscount) {
                        $amount               = $fdiscount - $maxDiscount;
                        $totalItemQtyDiscount = 0;
                    }

                    $this->discounts[$rule->getId()][$discountItem->getItemId()] = $amount;
                    $discount                                                    += $amount;
                    if ($totalItemQtyDiscount <= 0) {
                        break;
                    }
                }
            }
        }

        return $this;
    }
}
