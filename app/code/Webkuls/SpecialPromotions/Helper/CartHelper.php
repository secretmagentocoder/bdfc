<?php
/**
 * Webkulss Software
 *
 * @category  Webkulss
 * @package   Webkulss_Mpsplitcart
 * @author    Webkulss
 * @copyright Copyright (c) Webkulss Software Private Limited (https://Webkulss.com)
 * @license   https://store.Webkulss.com/license.html
 */

namespace Webkuls\SpecialPromotions\Helper;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;

/**
 * Webkulss Mpsplitcart BeforeViewCart Observer
 */
class CartHelper extends \Magento\Framework\App\Helper\AbstractHelper
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
        \Magento\Framework\App\Helper\Context $context,
        RuleRepositoryInterface $ruleRepository 
    ) {
        $this->_urlInterface = $urlInterface;
        $this->cart = $cart;
        $this->ruleFactory = $ruleFactory;
        $this->_json = $json;
        $this->productFactory = $productFactory;
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context);
    }

    /**
     * To get SkuWIse Rules
     *
     * @return void
     */
    public function getSkuWiseRules()
    {
        try {

            // retrieve quote items array
            $items = $this->cart->getQuote()->getAllItems();
            $quoteId = $this->cart->getQuote()->getId();
            $appliedIds = $this->cart->getQuote()->getAppliedRuleIds();
            $rulesIds=[];
            if ($appliedIds!='') {
                $rulesIds = explode(',',$appliedIds);
            }
            foreach($items as $item) 
            {
                $finalArray=[];
                if (!empty($rulesIds)) {
                    foreach ($rulesIds as $key => $ruleId) {
                        
                        $ruleConditionData = $this->ruleFactory->create()->load($ruleId);
                        $valid = $this->ruleConditions($ruleConditionData->getConditionsSerialized(), $item->getSku());
                        if ($valid!='') {
                            $finalArray[$key] = $ruleId;
                        }
                        
                    }
                    
                }
                if (!empty($finalArray)) {
                    $commaSeperated = implode(",",$finalArray)??'';
                    $item->setAppliedRuleIds($commaSeperated);
                    $item->save();
                }
               
                        
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
    }

    public function getHighRules(){
        $items = $this->cart->getQuote()->getAllItems();
        $quoteId = $this->cart->getQuote()->getId();
        $ruleConditionData = $this->ruleFactory->create()->getCollection()
                        ->addFieldToFilter(
                            'is_active',
                            1
                        );

        //return $ruleConditionData->getData();
        $finalArray=[];
        $finalArraySort=[];
        $finalSortedIds=[];
        foreach($items as $item) 
        {
            
            if ($ruleConditionData->getSize() > 0 ) {

                foreach ($ruleConditionData as $key => $rule) {
                    
                    $valid = $this->ruleConditions($rule->getConditionsSerialized(), $item->getSku());
                    if ($valid!='') {
                        $finalArray[$item->getId()][$rule->getId()] = $rule->getId();
                    }
                    
                }
                
            }
            
            
                    
        }
        if (!empty($finalArray)) {
            foreach ($finalArray as $key => $arrayVal){
                $finalArraySort[$key] = $this->getRuleDetailsByIds($arrayVal);
            }
        }
        if (!empty($finalArraySort)) {
            foreach ($finalArraySort as $skey => $sortVal){
                $finalSortedIds[$skey] = max(array_keys($sortVal, min($sortVal)));
            }
        }

        return $finalSortedIds;
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
        
        // $skus = [];
        $skus = '';
        $ruleDataArray = $this->_json->unserialize($conditons, true);
        if (isset($ruleDataArray['conditions'])) {
            $conditions = $ruleDataArray['conditions'];
            foreach ($conditions as $condition) {
                if (isset($condition['conditions'])) {
                    $productConditions = $condition['conditions'];
                    foreach ($productConditions as $productCondition) {
                        if (isset($productCondition['value'])) {
                            $skuValues = $productCondition['value'];
                            $skuValues = explode(",",$skuValues);
                           
                            foreach ($skuValues as $skuValue) {
                                if ($skuValue ==$sku) {
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
    /**
     * To get Cart Object
     *
     * @return object
     */
    public function getCartObject()
    {
        return $this->cart;
    }

    /**
     * To get Rule Object
     *
     * @return object
     */
    public function getRuleObject()
    {
        return $this->ruleFactory;
    }
     /**
     * To getapplied ids
     *
     * @return object
     */
    public function getAppliedRules()
    {
        return $this->cart->getQuote()->getAppliedRuleIds();
    }

    public function getRuleDetailsByIds($ids) {
        
        $ruleData = array();
        try {
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    
                    $rule = $this->ruleRepository->getById($id);
                    // $data = [
                    //     'sort_order' => $rule->getSortOrder(),
                    //     'qty' => $rule->getDiscountStep(),
                    //     'id' => $rule->getRuleId()
                    // ];
                    $data[$rule->getRuleId()] = $rule->getSortOrder();
                    $ruleData = $data;
                   // array_push($ruleData,$data);
                    
                    
                }
                
                
            } 
        } catch (\Exception $e) {
            
        }   
      
   
        return $ruleData;

    }

    public function getRuleByQtySku($sku, $qty) {
        $defaultRules=['by_percent','by_fixed','cart_fixed','buy_x_get_y'];
        $ruleConditionData = $this->ruleFactory->create()->getCollection()
                                                         ->addFieldToFilter(
                                                            'is_active',
                                                            1
                                                         )->addFieldToFilter(
                                                            'simple_action',
                                                            ['nin' => $defaultRules]
                                                         );
        $valid = $this->checkQtySkuRuleConditions($ruleConditionData, $qty, $sku);
    }  
    
    public function checkQtySkuRuleConditions($ruleConditionData, $qty, $sku) 
    {
        $finalArray=[];
        if($ruleConditionData->getSize() > 0) {
            foreach ($ruleConditionData as $rule) {
                
                $ruleDataArray = $this->_json->unserialize($rule->getConditionsSerialized(), true);
                if (!empty($ruleDataArray)) {
                      
                    if(isset($ruleDataArray['conditions'])) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $logger = $objectManager->create(\Webkuls\SpecialPromotions\Logger\Logger::class);
                        $logger->info("Hitted".json_encode($ruleDataArray['conditions']));
                        foreach($ruleDataArray['conditions'] as $condition) {
                            if($condition['attribute']=='qty' && $condition['value'] == $qty) {
                             
     
                                if(isset($condition['conditions'])) {

                                    foreach ($condition['conditions'] as $skuCondition) {
                                        if($condition['attribute']=='sku' && $condition['value'] == $sku) {

                                            $finalArray[$rule->getRuleId()]['sort_order'] = $rule->getSortOrder();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
       
        
        // echo "<pre>";
        // print_r($ruleDataArray);
        // echo "</pre>";
    }
}