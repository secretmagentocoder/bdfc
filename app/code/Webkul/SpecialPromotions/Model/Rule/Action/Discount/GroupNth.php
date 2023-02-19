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

class GroupNth extends DiscountAbstract
{
    /**
     * Calculate
     *
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
        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $productModel           = $this->product->create()->load($item->getProductId());
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $specialPriceFlag       = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag          = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        $childSpecialPriceFlag  = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        
        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }
        
        // if (!$this->promotionsHelper->checkPriorityRule($rule->getSortOrder())) {
        //     return $discountData;
        // }
        
        // $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds(),$qty);
        // if(!in_array($rule->getRuleId(),$highestRuleIds)) {
            
        //     return $discountData;
        // }
        
      
    
        $toDiscount             = $rule->getDiscountStep();
        $toDiscountBack         = $rule->getDiscountStep();
        $discountAmount         = $rule->getDiscountAmount();
        $maxAmount              = $rule->getMaxDiscount();
        $arrayItemsPrice        = [];
        $qties =[];
        $finalqty=0;
        $this->currentProductId = $item->getProductId();
        
        if ($discountAmount) {
            $subTotal = 0;
            $finalqty = $item->getQty()/$toDiscountBack;
    
           // $finalqty = ($item->getQty() * $toDiscount) / $toDiscount;
            if ($finalqty) {
                $toDiscount = (int)$finalqty;
            }
            
            $subTotal = ($item->getPrice() * $toDiscount) * $toDiscountBack;
           
            // $logger->info("GNth-Quote item id".$item->getId()." subTotal(".$item->getPrice()."): ".$subTotal);
            // foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
            //     $currentQty = $currentItem->getQty();
            //     array_push($qties,$currentQty);
            //     for ($i=1; $i<= $currentQty; $i++) {
            //         $arrayItemsPrice [] = [
            //             'product_id' => $currentItem->getProductId(),
            //             'price'      => $currentItem->getPrice()
            //         ];
            //     }
            //     $subTotal = $subTotal+($currentItem->getPrice()*$currentQty);
            // }
            
            //if ($toDiscount > 0 && count($arrayItemsPrice) >= $toDiscount) {
                

                    // $arrayItemsPrice = $this->promotionsHelper->sortViaPrice($arrayItemsPrice);
                    // list($groupPrice, $proCount) = $this->getNGroupPrice(
                    //     $arrayItemsPrice,
                    //     $discountAmount,
                    //     $toDiscount
                    // );
                    // $discountAmount = $groupPrice - $discountAmount * floor(count($arrayItemsPrice)/$toDiscount);
                    // if ($maxAmount) {
                    //     if ($maxAmount <= $discountAmount) {
                    //         $discountAmount = $maxAmount;
                    //     }
                    // }
               
               
                $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
                if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
                    $discountAmount=0;
                } 
                if ($discountAmount > 0) {
                    
                    $discountAmount =  ($discountAmount * $toDiscount);
                    $discountAmount = round(($subTotal - $discountAmount), 2);
                    $discountData->setAmount($discountAmount);
                    $discountData->setBaseAmount($discountAmount);
                    $discountData->setOriginalAmount($discountAmount);
                    $discountData->setBaseOriginalAmount($discountAmount);
                    // $discountQty = $discountAmount/$subTotal;
                    // $discountData->setAmount($discountQty * $itemPrice * $qty);
                    // $discountData->setBaseAmount($discountQty * $baseItemPrice * $qty);
                    // $discountData->setOriginalAmount($discountQty * $itemOriginalPrice * $qty);
                    // $discountData->setBaseOriginalAmount($discountQty * $baseItemOriginalPrice * $qty);
                }
            //}
        }
        return $discountData;
    }

    /**
     * This function loops every items and calls the validate amount function
     *
     * @param array $items
     * @param integer $groupAmount
     * @param integer $toDiscountItems
     * @param integer $flag
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
     * @param integer $groupAmount
     * @param integer $flag
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

    public function getHighestPriorityRule($ids,$qties) {

    
        $arrayRules = [];
        $ruleId =[];
        $rulesArray =[];
        $sortRules=[];
    
        if ($ids!='') {
            $ids = explode(',', $ids);
            $arrayRules = $this->promotionsHelper->getRuleDetailsByIds($ids);
        }
       
        if (!empty($arrayRules)) {

            foreach ($arrayRules as $key => $ruleqty) {
               
                if (in_array($ruleqty['qty'],$qties)) {
                   
                    $sortRules[$ruleqty['id']]= $ruleqty['sort_order'];
                   // $sortRules[$key]['id']= $ruleqty['id'];
                    
                } 
                //  else {

                //     if(count($qties) == 1) {
                         
                //         for ($i=0; $i<count($qties); $i++) {

                //             if ($ruleqty['qty'] = $qties[$i]) {

                //                 $sortRules[$ruleqty['id']]= $ruleqty['sort_order'];
                //             }
                //         }
                //     }
                // }
               
                
            }
        }
        
       
        if (!empty($sortRules)) {
            
            $ruleId = array_keys($sortRules, min($sortRules));
            
        }   
      
        
        return $ruleId;
    }
}
