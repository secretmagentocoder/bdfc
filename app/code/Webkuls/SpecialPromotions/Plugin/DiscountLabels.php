<?php

namespace Webkuls\SpecialPromotions\Plugin;

class DiscountLabels 
{

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    protected $ruleRepositoryInterface;


     /**
    
     * @param \Magento\Checkout\Model\SessionFactory           $sessionFactory
     * @param \Magento\Quote\Model\QuoteFactory                $quoteFactory
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Checkout\Model\SessionFactory $sessionFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Webkuls\SpecialPromotions\Logger\Logger $logger,
        \Webkuls\SpecialPromotions\Helper\Data $promotionsHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepositoryInterface
    
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->logger = $logger;
        $this->promotionsHelper = $promotionsHelper;
        $this->cart = $cart;
        $this->ruleRepositoryInterface = $ruleRepositoryInterface;
       
    }

	public function afterAddDiscountDescription(
        \Magento\SalesRule\Model\RulesApplier $subject, 
        $result,
        $address, 
        $rule

    )
	{
       
        $qties = $this->getQuoteQuantities();
        $rulesids = $this->getAppliedIds();
        $description=[];
        if ($rulesids!='') {

            //$mainRuleIds = $this->getHighestPriorityRule($rulesids,$qties);
            $mainRuleIds = explode(',',$rulesids);
            
        } 
        if (!empty($mainRuleIds)) {
             foreach($mainRuleIds as $ruleId) {
                $rule = $this->ruleRepositoryInterface->getById($ruleId);
                $label = $rule->getDescription();
                if (strlen($label)) {
                    $description[$rule->getRuleId()] = $label;
                }
                
             }
             $address->setDiscountDescriptionArray($description);
        }
        
	}

    public function getAppliedIds()
    {
        $quoteId = $this->sessionFactory->create()->getQuote()->getId();
        $quote = $this->quoteFactory->create()->loadActive($quoteId);
        $salesruleIds = $quote->getAppliedRuleIds();
        return $salesruleIds;
    }

    /**
     * To get Highest Priority Rule
     *
     * @param string $ids
     * @return array
     */
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
                // else {

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

    public function getQuoteQuantities() {

        $qties =[];
       
        try {

            foreach ($this->cart->getQuote()->getAllItems() as $currentItem) {
                if ($currentItem->getData('parent_item_id') && $currentItem->getPrice() <= 0) {
                    continue;
                }
                $currentItemQty = $currentItem->getQty();
                array_push($qties,$currentItemQty);
            }
        } catch (\Exception $e) {

        }
        
        return $qties;
    }

}