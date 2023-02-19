<?php

namespace Ecommage\SalesRule\Plugin\Model\Rule\Action\Discount;

use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;

/**
 * Class GroupNPercentDiscount
 */
class GroupNPercentDiscount extends GroupNth
{
    /**
     * @var array
     */
    protected $itemPrices = [];
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * GroupNPercentDiscount constructor.
     *
     * @param Validator                             $validator
     * @param \Webkul\SpecialPromotions\Helper\Data $promotionsHelper
     * @param DataFactory                           $discountDataFactory
     * @param Registry                              $registry
     */
    public function __construct(
        Validator $validator,
        \Webkul\SpecialPromotions\Helper\Data $promotionsHelper,
        DataFactory $discountDataFactory,
        Registry $registry
    ) {
        $this->validator        = $validator;
        parent::__construct($promotionsHelper, $discountDataFactory, $registry);
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
                    $price  = $discountItem->getPrice();
                    if (isset($this->itemPrices[$discountItem->getItemId()])) {
                        $price = $this->itemPrices[$discountItem->getItemId()][$rule->getData('wkrulesrule')] ?? $price;
                    }

                    if ($totalItemQtyDiscount > 0 && $totalItemQtyDiscount > $discountItem->getQty()) {
                        $amount               = ($discountAmount / 100) * ($discountItem->getQty() * $price);
                        $totalItemQtyDiscount -= $discountItem->getQty();
                    } elseif ($totalItemQtyDiscount > 0 && $discountItem->getQty() == $totalItemQtyDiscount) {
                        $amount               = ($discountAmount / 100) * ($discountItem->getQty() * $price);
                        $totalItemQtyDiscount = 0;
                    } elseif ($totalItemQtyDiscount > 0 && $discountItem->getQty() > $totalItemQtyDiscount) {
                        $diffQty              = $discountItem->getQty() - $totalItemQtyDiscount;
                        $amount               = ($discountAmount / 100) * ($diffQty * $price);
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
        //$baseItemPrice         = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice     = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
        $this->itemPrices[$item->getItemId()] = [
            0 => $item->getPrice(),         //Price (Special Price if Set)
            1 => $itemOriginalPrice,        //Price After Previous Discount(s)
            2 => $baseItemOriginalPrice     //Original Price
        ];

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
}
