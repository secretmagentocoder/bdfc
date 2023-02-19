<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SpecialPromotions
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SpecialPromotions\Helper;

use Magento\Customer\Model\Session;
use \Magento\Checkout\Model\Session as CheckoutSession;
use Webkul\SpecialPromotions\Plugin\Model\Rule\Metadata\ValueProvider;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use \Webkul\SpecialPromotions\Helper\CartHelper;

/**
 * SpecialPromotions data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     *
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        RuleRepositoryInterface $ruleRepository,
        CheckoutSession $session,
        \Webkul\SpecialPromotions\Logger\Logger $logger,
        CartHelper $cartHelper
    ) {
        $this->productFactory = $productFactory;
        $this->date = $date;
        $this->ruleRepository = $ruleRepository;
        $this->session = $session;
        $this->logger = $logger;
        $this->cartHelper = $cartHelper;
        parent::__construct($context);
       
    }

    /**
     * This function returns the product model
     *
     * @param integer $id
     * @return Product
     */
    public function getProduct($id)
    {
        return $this->productFactory->create()->load($id);
    }

    /**
     * This sorts the array with price
     *
     * @param array $toSort
     * @return array
     */
    public function sortViaPrice($toSort)
    {
        $tempArray = [];
        $numOfValues = count($toSort);
        for ($i=0; $i < ($numOfValues-1); $i++) {
            $position = $i;
            for ($j = $i+1; $j < $numOfValues; $j++) {
                if ($toSort[$position]['price'] > $toSort[$j]['price']) {
                    $position = $j;
                }
            }
            if ($position != $i) {
                $letsSwap = $toSort[$i];
                $toSort[$i] = $toSort[$position];
                $toSort[$position] = $letsSwap;
            }
        }
        return $toSort;
    }

    /**
     * Check Nth Percent Discount is available
     *
     * @param object $item
     * @param object $rule
     * @param Object $productModel
     * @return boolean
     */
    public function canGetDiscountNPercent($item, $rule, $productModel)
    {
        $itemSKU    = $item->getData('sku');
        $skus       = explode(',', $rule->getData('promo_skus'));
        $count      = 0;
        foreach ($skus as $sku) {
            if ($itemSKU == $sku) {
                $count = 1 ;
            }
        }
        if ($count == 1) {
            return true ;
        } else {
            return false;
        }
    }

    /**
     *  Function will not discount if configurable product's child have special price
     *
     * @param object $item
     * @param object $productModel
     * @return boolean
     */
    public function skipItemIfConfigurableChildSpecialPrice($item, $productModel)
    {
        $sku = $item->getSku();
        $skipItemIfConfigurableChildSpecialPrice = $this->scopeConfig->getValue(
            'customization/skipsettings/skip_configurable_item_special_price'
        );
        $check = 0;
        if ($skipItemIfConfigurableChildSpecialPrice) {
            if ($productModel->getTypeId() == "configurable") {
                $date = $this->date->gmtDate();
                $childProductId = $this->productFactory->create()->getIdBySku($sku);
                $child = $this->productFactory->create()->load($childProductId);
                if ($child->getSpecialToDate() != "") {
                    $specialPriceTo = $child->getSpecialToDate();
                } else {
                    $specialPriceTo = $date;
                }
                if ($child->getSpecialPrice() != "" && (strtotime($specialPriceTo) >= strtotime($date))) {
                        return true;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    /**
     * This function will not allow the item to have discount if it has tier price set
     *
     * @param object $item
     * @param object $productModel
     * @return boolean
     */
    public function skipItemWithTierPrice($item, $productModel)
    {
        $configSkipItemWithTierPrice = $this->scopeConfig
                                    ->getValue(
                                        'customization/skipsettings/skip_item_tier_price',
                                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                    );
               
        if ($configSkipItemWithTierPrice) {
            if ($productModel->getTypeId() == "configurable") {
                $date = $this->date->gmtDate();
                $sku = $item->getSku();
                $childProductId = $this->productFactory->create()->getIdBySku($sku);
                $child = $this->productFactory->create()->load($childProductId);
                $tierPrice = $child->getTierPrice();
                if (!empty($tierPrice)) {
                    return true;
                }
                return false;
            }
            $tierPrice = $productModel->getTierPrice();
            if (!empty($tierPrice)) {
                return true;
            }
        }
        return false;
    }
    
   /**
    * This function is used to skip the item having special price
    *
    * @param  object  $item
    * @param  object  $rule
    * @param  object  $productModel
    * @return bool
    */
    public function checkSpecialPriceSkip($item, $rule, $productModel)
    {
        $configSkipSpecialPriceValue = $this->scopeConfig
            ->getValue(
                'customization/skipsettings/skip_item_special_price',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        $specailPrice = $productModel->getSpecialPrice();
        $date = $this->date->gmtDate();
        if ($productModel->getSpecialToDate() != "") {
            $specialPriceTo = $productModel->getSpecialToDate();
        } else {
            $specialPriceTo = $date;
        }
        if ($specailPrice != "" && (strtotime($specialPriceTo) >= strtotime($date))) {
            // rule setting set to no
            if ($rule->getData('wkrulesrule_skip_rule') == 2) {
                return false;
            } elseif ($rule->getData('wkrulesrule_skip_rule') == 1) {
                return true;//rule setting set to yes
            } elseif ($rule->getData('wkrulesrule_skip_rule') == 0) {
                // rule setting set to use default
                if ($configSkipSpecialPriceValue) {
                    return true;
                }
            } elseif ($rule->getData('wkrulesrule_skip_rule') == 3) {
                //check product is discounted or not
                if ($productModel->getFinalPrice() < $productModel->getPrice()) {
                    return true;
                }
            }
            if ($configSkipSpecialPriceValue) {
                return true;
            } else {
                return false;
            }
        }
         return false;
    }

     /**
      * Get Discount Types
      *
      * @param bool $asOptions
      *
      * @return array
      */
    public function getDiscountTypes()
    {
        $types = [
            ValueProvider::WK_CHEAPEST => __('The Cheapest, also for Buy 1 get 1 free'),
            ValueProvider::WK_MOST_EXPENSIVE => __('Most Expensive'),
            ValueProvider::WK_MONEY_AMOUNT => __('Get $Y for each $X spent'),

            ValueProvider::WK_EACH_NTH_PERCENT_DISCOUNT => __('Percent Discount: each 2-th, 4-th, 6-th with 10% 0ff'),
            ValueProvider::WK_EACH_NTH_FIXED_DISCOUNT => __('Fixed Discount: each 3-th, 6-th, 9-th with $10 0ff'),
            ValueProvider::WK_EACH_NTH_FIXED_PRICE => __('Fixed Price: each 5th, 10th, 15th for $67'),

            ValueProvider::WK_EACH_PAFT_NTH_PERCENT =>
                __('Percent Discount: each 1st, 3rd, 5th with 10% 0ff after 5 items added to the cart'),
            ValueProvider::WK_EACH_PAFT_NTH_FIXED =>
                __('Fixed Discount: each 3d, 7th, 11th with $10 0ff after 5 items added to the cart'),
            ValueProvider::WK_EACH_PAFT_NTH_FIXED_PRICE =>
                __('Fixed Price: each 5th, 7th, 9th for $79.90 after 5 items added to the cart'),

            ValueProvider::WK_GROUP_N => __('Fixed Price: Each 5 items for $50'),
            ValueProvider::WK_GROUP_N_DISCOUNT => __('Percent Discount: Each 5 items with 15% off'),

            ValueProvider::WK_BUY_X_GET_N_PERCENT_DISCOUNT => __('Percent Discount: Buy X get Y Free'),
            ValueProvider::WK_BUY_X_GET_N_FIXED_DISCOUNT => __('Fixed Discount:  Buy X get Y with $5 Off'),
            ValueProvider::WK_BUY_X_GET_N_FIXED_PRICE => __('Fixed Price: Buy X get Y for $7.45'),

            ValueProvider::WK_PRODUCT_SET_DISCOUNT => __('Percent discount for product set'),
            ValueProvider::WK_PRODUCT_SET_DISCOUNT_FIXED => __('Fixed price for product set'),

        ];

        return $types;
    }

    /**
     * Get File Path
     *
     * @param object $rule
     * @return mixed|string
     */
    public function getFilePath($rule)
    {
        
        $rule = implode('_', array_map('ucfirst', explode('_', $rule)));
        $rule = str_replace('_', '', $rule);
        $rule = 'Webkul\SpecialPromotions\Model\Rule\Action\Discount\\' . $rule;

        return $rule;
    }

    /**
     * Check Module Status
     *
     * @return bool
     */
    public function checkModuleStatus()
    {
        return $this->scopeConfig
        ->getValue(
            'customization/settings/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * To get Rules by Ids
     *
     * @param array $ids
     * @return array
     */
    public function getRuleDetailsByIds($ids) {
        
        $ruleData = array();
        try {
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    
                    $rule = $this->ruleRepository->getById($id);
                    $data = [
                        'sort_order' => $rule->getSortOrder(),
                        'qty' => $rule->getDiscountStep(),
                        'id' => $rule->getRuleId()
                    ];
                    array_push($ruleData,$data);
                    
                    
                }
                
                
            } 
        } catch (\Exception $e) {
            
        }   
      
   
        return $ruleData;

    }

   
    /**
     * To get Highest Priority Rule
     *
     * @param string $ids
     * @return array
     */

    public function getHighestPriorityRule($appliedIds) {

        // $items = $this->cartHelper->getCartObject()->getQuote()->getAllItems();
        // $quoteId = $this->cartHelper->getCartObject()->getQuote()->getId();
        // $appliedIds = $this->cartHelper->getCartObject()->create()->load($quoteId)->getAppliedRules();
        //$appliedIds = $this->cartHelper->getAppliedRules();
        
        $appliedRulesIds=[];
        if ($appliedIds!='') {
            $appliedRulesIds = explode(',',$appliedIds);
        }
       
  
        //
       
        // $ruleId =[];
        // $rulesIds=[];
        // if ($appliedIds!='') {
        //     $rulesIds = explode(',',$appliedIds);
        // }
      
        // foreach($items as $item) 
        // {
            
        //     $finalArray=[];
        //     $sortRules=[];
        //     if (!empty($rulesIds)) {
        //         foreach ($rulesIds as $key => $ruleId) {
                    
        //             $ruleConditionData = $this->cartHelper->getRuleObject()->create()->load($ruleId);
        //             $valid = $this->cartHelper->ruleConditions($ruleConditionData->getConditionsSerialized(), $item->getSku());
        //             if ($valid!='') {
        //                 $finalArray[$key] = $ruleId;
        //             }
                    
        //         }
                
        //     }
            
        //     if (!empty($finalArray)) {
        //         $arrayRules = $this->getRuleDetailsByIds($finalArray);
        //         if (!empty($arrayRules)) {

        //             foreach ($arrayRules as $key => $ruleqty) {
        //                 $sortRules[$ruleqty['id']]= $ruleqty['sort_order']; 
        //             }
                    
        //             if (!empty($sortRules)) {
        //                 $ruleId = max(array_keys($sortRules, min($sortRules)));
                        
        //             } 
                    
        //         }
            
        //     }
           
                    
        // }
     
        return $appliedRulesIds;
    }
    // public function getHighestPriorityRule($ids,$qties,$itemId) {

        
    //     $arrayRules = [];
    //     $ruleId =[];
    //     //$ruleId ='';
    //     $rulesArray =[];
    //     $sortRules=[];
    
    //     if ($ids!='') {
    //         $ids = explode(',', $ids);
    //         $arrayRules = $this->getRuleDetailsByIds($ids);
    //     }
        
    //     if (!empty($arrayRules)) {

    //         foreach ($arrayRules as $key => $ruleqty) {
               
              
    //            // if (in_array($ruleqty['qty'],$qties)) {
    //                // $sortRules[$ruleqty['id']]= $ruleqty['sort_order'];
    //                 $sortRules[$ruleqty['id']]= $ruleqty['sort_order'];
    //                // $sortRules[$key]['id']= $ruleqty['id'];
    //             //} 
               
    //         }
    //     }
    //     //$this->getLoggerObject()->info("its for ".$itemId."ss".json_encode($sortRules));
    //     if (!empty($sortRules)) {
            
    //         //$ruleId = array_keys($sortRules, min($sortRules));
    //         $ruleId = array_keys($sortRules, min($sortRules));
            
    //     }   
    
    //     return $ruleId;
    // }

    /**
     * To get Logger Object Instance
     *
     * @return object
     */
    public function getLoggerObject() {

        return $this->logger;
    }
}
