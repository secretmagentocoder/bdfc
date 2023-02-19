<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitcart
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SpecialPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use \Webkul\SpecialPromotions\Helper\Data;
/**
 * Webkul Mpsplitcart BeforeViewCart Observer
 */
class BeforeViewCart implements ObserverInterface
{
   
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $_urlInterface;

    protected $ruleFactory;
    protected $productFactory;

    protected $_checkoutSession;

     /**
    * @var \Magento\Framework\Serialize\Serializer\Json
    */
   protected $_json;

    /**
     * @param ManagerInterface                $messageManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Data $dataHelper
        
    ) {
        $this->_urlInterface = $urlInterface;
        $this->cart = $cart;
        $this->ruleFactory = $ruleFactory;
        $this->_json = $json;
        $this->productFactory = $productFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * [executes on controller_action_predispatch_checkout_index_index event]
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
           
            // retrieve quote items array
            $items = $this->cart->getQuote()->getAllItems();
            $quoteId = $this->cart->getQuote()->getId();
            $appliedIds = $this->cart->getQuote()->getAppliedRuleIds();
            // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // $logger = $objectManager->create(\Webkul\SpecialPromotions\Logger\Logger::class);
            // $logger->info("appliedIds".json_encode($appliedIds));
            
            $rulesIds=[];
            $sortRules=[];
            $finalRules=[];
            $resultArray=[];
            $defaultRules=['by_percent','by_fixed','cart_fixed','buy_x_get_y'];
            if ($appliedIds!='') {
                $rulesIds = explode(',',$appliedIds);
            }
           
            $sortedRulesIds=[];
            $coupons=[];
            foreach($items as $item) 
            {
                $finalArray=[];
                if (!empty($rulesIds)) {
                    foreach ($rulesIds as $key => $ruleId) {
                        
                        $ruleConditionData = $this->ruleFactory->create()->load($ruleId);
                        
                        if ($ruleConditionData->getCouponCode()!='' || in_array($ruleConditionData->getSimpleAction(),$defaultRules)){
                            if(!in_array($ruleId,$coupons)){
                                array_push($coupons,$ruleId);
                            }
                            
                        } else {

                            $valid = $this->ruleConditions($ruleConditionData->getConditionsSerialized(), $item->getSku());
                            if ($valid!='') {
                                if(!in_array($ruleId,$finalArray)){
                                    //array_push($coupons,$ruleId);
                                    $finalArray[$key] = $ruleId;
                                }
                             
                            }
                        }

                    }
                    
                }
               
                if (count($finalArray) > 1) {

                    $arrayRules =$this->dataHelper->getRuleDetailsByIds($finalArray);
                    if (!empty($arrayRules)) {

                        foreach ($arrayRules as $key => $ruleqty) {
                            $sortRules[$ruleqty['id']]= $ruleqty['sort_order']; 
                        }
                        
                        if (!empty($sortRules)) {
                            $ruleidCheck = max(array_keys($sortRules, min($sortRules)));
                            array_push($finalRules,$ruleidCheck); 
                            
                        } 
                    
                    }
                   
                } else {

                    foreach($finalArray as $singleOne) {
                        array_push($finalRules,$singleOne);
                    }
                   
                    // $finalRules = $ruleId
                   
                }
                     
            }
            

            if (!empty($finalRules)) { 
                
                $resultArray = array_merge($finalRules, $coupons);
            }
            if (!empty($resultArray)) {

                $setRules = implode(",",$resultArray);
                echo "<span style='display: none;'>".$setRules."</span>";
                $this->cart->getQuote()->setAppliedRuleIds($setRules);
                $this->cart->getQuote()->save();

                foreach($items as $item) {
                    $item->setAppliedRuleIds($setRules);
                    $item->save();
                }
            }
            
           
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
    }
    /**
     * To check the condition with sky
     *
     * @param string $conditons
     * @param string $sku
     * @return string
     */
    public function ruleConditions($conditons, $sku) 
    {
       
        $skus = '';
        $ruleDataArray = $this->_json->unserialize($conditons, true);
        
        if (isset($ruleDataArray['conditions'])) {
            $conditions = $ruleDataArray['conditions'];
            foreach ($conditions as $condition) {
                if (isset($condition['conditions'])) {
                    $productConditions = $condition['conditions'];
                    foreach ($productConditions as $productCondition) {
                        if ($productCondition['attribute']=='sku' && isset($productCondition['value'])) {
                            $skuValues = $productCondition['value'];
                            $skuValues = explode(",",$skuValues);
                           
                            foreach ($skuValues as $skuValue) {
                                if ($skuValue == $sku) {
                                    $skus = $skuValue;
                                }
                                //$skus[] = $skuValue;
                            }
                        }
                    }
                }
            }
        }
        return $skus;
        //$uniqSkus = array_unique($skus);
      
        
    }
    
}