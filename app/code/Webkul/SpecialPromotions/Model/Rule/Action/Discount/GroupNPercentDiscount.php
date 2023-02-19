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

class GroupNPercentDiscount extends DiscountAbstract
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

        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $logger = $objectManager->create(\Webkul\SpecialPromotions\Logger\Logger::class);
        // $logger->info("GNP-Quote item id".$item->getId()." qty: ".$qty);

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData           = $this->discountFactory->create();

        //$this->promotionsHelper->getLoggerObject()->info("GroupNPercentDiscount highestRuleIds ids".json_encode($item->getAppliedRuleIds()));

        $itemPrice              = $this->validator->getItemPrice($item);
        $baseItemPrice          = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice      = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice  = $this->validator->getItemBaseOriginalPrice($item);
        $productModel           = $this->product->create()->load($item->getProductId());
        $childSpecialPriceFlag  = $this->promotionsHelper->skipItemIfConfigurableChildSpecialPrice(
            $item,
            $productModel
        );
        $specialPriceFlag       = $this->promotionsHelper->checkSpecialPriceSkip($item, $rule, $productModel);
        $tierPriceFlag          = $this->promotionsHelper->skipItemWithTierPrice($item, $productModel);
        
        if ($specialPriceFlag || $tierPriceFlag || $childSpecialPriceFlag) {
            return $discountData;
        }
        
        // 
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
            $finalqty = ($item->getQty()/$toDiscountBack);
            if ($finalqty) {
                $toDiscount = (int)$finalqty;
            }
            $subTotal = ($item->getPrice() * $toDiscount) * $toDiscountBack;
            $priceTotal = ($item->getPrice() * $toDiscountBack);
            
            //$discountFinalAmount = ($priceTotal * $discountAmount)/100;
            
            $discountAmount = ($priceTotal * $discountAmount)/100;
            if ($finalqty) {
                $discountAmount = $discountAmount * $toDiscount;
            }
           // $logger->info("GNP-Quote item id".$item->getId()." subTotal(".$item->getPrice()."): ".$subTotal);
            // foreach ($item->getQuote()->getAllVisibleItems() as $currentItem) {
            //     if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
            //         continue;
            //     }
            //     $currentItemQty = $currentItem->getQty();
            //     for ($i=1; $i<= $currentItemQty; $i++) {
            //         $arrayItemsPrice [] = [
            //             'product_id' => $currentItem->getProductId(),
            //             'price'      => $currentItem->getPrice()
            //         ];
            //     }
            //     $subTotal = $subTotal+($currentItem->getPrice()*$currentItemQty);
            // }
            //if ($toDiscount > 0) {
              
                //$this->promotionsHelper->getLoggerObject()->info("GroupNPercentDiscount highestRuleIds ids".$rule->getRuleId());
                //$this->promotionsHelper->getLoggerObject()->info("GroupNPercentDiscount s ids".json_encode($highestRuleIds));
                //$highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds(), $qties,$item->getId());
                
              //if(!empty($highestRuleIds) && in_array($rule->getRuleId(),$highestRuleIds)) {
                    
                    // $arrayItemsPrice = $this->promotionsHelper->sortViaPrice($arrayItemsPrice);
                    // list($groupPrice, $productCount) = $this->getNGroupPrice(
                    //     $arrayItemsPrice,
                    //     $discountAmount,
                    //     $toDiscount,
                    //     1
                    // );
                    // $discountAmount = $groupPrice * ($discountAmount / 100);
                    // if ($maxAmount) {
                    //     if ($maxAmount <= $discountAmount) {
                    //         $discountAmount = $maxAmount;
                    //     }
                    // }   
                // } else {
                //     $discountAmount=0;
                // }
                if ($maxAmount) {
                    if ($maxAmount <= $discountAmount) {
                        $discountAmount = $maxAmount;
                    }
                }  
                $highestRuleIds = $this->promotionsHelper->getHighestPriorityRule($item->getAppliedRuleIds());
                if(!empty($highestRuleIds) && !in_array($rule->getRuleId(),$highestRuleIds)) {
                    $discountAmount=0;
                }   


                if ($discountAmount > 0) {
                   // $discountAmount =  ($discountAmount * $toDiscount);
                    // $discountAmount = round($discountAmount/$subTotal*($item->getPrice()*$qty), 2);
                    //$discountAmount = round(($subTotal*$discountAmount)/100, 2);
                    $discountData->setAmount($discountAmount);
                    $discountData->setBaseAmount($discountAmount);
                    $discountData->setOriginalAmount($discountAmount);
                    $discountData->setBaseOriginalAmount($discountAmount);
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
     * @param int $flag
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
     * @param int $flag
     * @return array
     */
    protected function checkAmountValid($i, $toDiscountItems, $items, $groupAmount, $flag)
    {
        $countItems = $groupPrice = 0;
        $groupPrice2=0;
        $countToDiscountItems = 0;
        $productCount = 0;
        foreach ($items as $key => $value) {
                $countToDiscountItems ++;
            if ($countToDiscountItems >= $i) {
                $countItems ++;
                $groupPrice2 = $groupPrice2 + $value['price'];
                if ($countToDiscountItems%$toDiscountItems==0) {
                    $countToDiscountItems;
                    $groupPrice = $groupPrice2;
                }
                if ($this->currentProductId == $value["product_id"]) {
                    $productCount ++;
                }
            }
        }
        if ($flag && ($groupPrice >= ($groupPrice * ($groupAmount / 100)))) {
            return [$groupPrice,$productCount];
        } elseif ($groupPrice <= $groupAmount) {
            return [0,$productCount];
        } else {
            return [$groupPrice, $productCount];
        }
    }

    //  /**
    //  * To get Highest Priority Rule
    //  *
    //  * @param string $ids
    //  * @return array
    //  */
    // public function getHighestPriorityRule($ids,$qties) {

    
    //     $arrayRules = [];
    //     $ruleId =[];
    //     $rulesArray =[];
    //     $sortRules=[];
    
    //     if ($ids!='') {
    //         $ids = explode(',', $ids);
    //         $arrayRules = $this->promotionsHelper->getRuleDetailsByIds($ids);
    //     }
       
    //     if (!empty($arrayRules)) {

    //         foreach ($arrayRules as $key => $ruleqty) {
               
    //            if (in_array($ruleqty['qty'],$qties)) {
                   
    //                 $sortRules[$ruleqty['id']]= $ruleqty['sort_order'];
    //                // $sortRules[$key]['id']= $ruleqty['id'];
                    
    //            }   
    //         }
    //     }
    
        
    //     if (!empty($sortRules)) {
            
    //         $ruleId = array_keys($sortRules, min($sortRules));
            
    //     }   
      
        
    //     return $ruleId;
    // }

    
}
