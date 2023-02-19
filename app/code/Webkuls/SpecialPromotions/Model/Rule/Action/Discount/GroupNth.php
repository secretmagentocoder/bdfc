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

class GroupNth extends DiscountAbstract
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
        $discountData           = $this->discountFactory->create();

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
        $toDiscount             = $rule->getDiscountStep();
        $discountAmount         = $rule->getDiscountAmount();
        $maxAmount              = $rule->getMaxDiscount();
        $arrayItemsPrice        = [];
        
        $this->currentProductId = $item->getProductId();
        
        if ($discountAmount) {
            foreach ($this->cart->getQuote()->getAllVisibleItems() as $currentItem) {
                if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
                    continue;
                }
                $currentQty = $currentItem->getQty();
                for ($i=1; $i<= $currentQty; $i++) {
                    $arrayItemsPrice [] = [
                        'product_id' => $currentItem->getProductId(),
                        'price'      => $currentItem->getPrice()
                    ];
                }
            }
            if ($toDiscount > 0 && count($arrayItemsPrice) >= $toDiscount) {
                $arrayItemsPrice = $this->promotionsHelper->sortViaPrice($arrayItemsPrice);
                list($groupPrice, $proCount) = $this->getNGroupPrice(
                    $arrayItemsPrice,
                    $discountAmount,
                    $toDiscount
                );
                $discountAmount = $groupPrice - $discountAmount * floor(count($arrayItemsPrice)/$toDiscount);
                if ($maxAmount) {
                    if ($maxAmount <= $discountAmount) {
                        $discountAmount = $maxAmount;
                    }
                }
                if ($discountAmount > 0) {
                    $discountAmount = $discountAmount / count($arrayItemsPrice)*$qty;
                    $discountData->setAmount($discountAmount);
                    $discountData->setBaseAmount($discountAmount);
                    $discountData->setOriginalAmount($discountAmount);
                    $discountData->setBaseOriginalAmount($discountAmount);
                }
            }
        }
        return $discountData;
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
        list($groupPrice, $count) = $this->checkAmountValid(1, $toDiscountItems, $items, $groupAmount, $flag);
            
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
        $groupPrice2=0;
        $countToDiscountItems = $productCount = 0;
        foreach ($items as $key => $value) {
            $countToDiscountItems ++;
            if ($countToDiscountItems >= $i) {
                $countItems ++;
                    $groupPrice2 = $groupPrice2 + $value['price'];
                if ($countToDiscountItems%$toDiscountItems==0) {
                    $groupPrice = $groupPrice2;
                }
                if ($this->currentProductId == $value["product_id"]) {
                    $productCount ++;
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
}
