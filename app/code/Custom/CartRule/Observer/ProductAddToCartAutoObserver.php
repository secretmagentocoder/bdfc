<?php

namespace Custom\CartRule\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class ProductAddToCartAutoObserver implements ObserverInterface
{
    protected $_product;

    protected $_cart;

    protected $formKey;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart
    ){
        $this->_product = $product;
        $this->formKey = $formKey;
        $this->_cart = $cart;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /*$product = $observer->getEvent()->getData('product');*/
        $items = $this->_cart->getQuote()->getAllVisibleItems();

        $all_items_ids = [];
        foreach($items as $item) {
            $product_id = $item->getProductId();
            $product_sku = $item->getSku();

            $all_items_ids [$product_id]= $product_sku;
        }

        $getOfferFreeProductIfExists = [];
        if (!empty($all_items_ids)) {
            $getOfferFreeProductIfExists = $this->getOfferFreeProductIfExists($all_items_ids);
        }

        if (!empty($getOfferFreeProductIfExists)) {
        	array_unique($getOfferFreeProductIfExists);
        	foreach ($getOfferFreeProductIfExists as $key => $value) {
        		$product_sku = $value;
	            $product_data = $this->_product->create();
				$product_data->load($product_data->getIdBySku($product_sku));
	            $product_id = $product_data->getId();

	            $params = array();
        		$params = array(
	                'form_key' => $this->formKey->getFormKey(),
	                'product' => $product_id, //product Id
	                'qty'   => 1 //quantity of product                
	            );
	            $_product = $this->_product->create()->load($product_id);      
	            $this->_cart->addProduct($_product, $params);
	            $this->_cart->save();
        	}
        }
    }

    public function getOfferFreeProductIfExists($all_items_ids)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $currentWebsiteId = $_storeManager->getStore()->getWebsiteId();

        $rules = $objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules = $rules->getCollection()->addIsActiveFilter()->addWebsiteFilter($currentWebsiteId)->addFieldToFilter('simple_action', 'buy_x_get_n_percent_discount');

        $freeItemSku = [];
        foreach ($rules as $rule) {
            $is_active = $rule->getIsActive();
            if (isset($is_active) && $is_active == '1') {
                // $rule_data = $rule->getData();
                // print_r($rule_data);
                $rule_name = $rule->getName();
                $rule_simple_action = $rule->getSimpleAction();
                if (isset($rule_simple_action) && $rule_simple_action == 'buy_x_get_n_percent_discount') {
                    // 
                    $rule_wkrulesrule_nqty = $rule->getWkrulesruleNqty();
                    $rule_discount_step = $rule->getDiscountStep();
                    $rule_promo_skus = $rule->getPromoSkus();
                    $rule_conditions_serialized = $rule->getConditionsSerialized();

                    if (count($all_items_ids) >= $rule_discount_step) {
                        // 
                        $rule_conditions_serialized_arr = json_decode($rule_conditions_serialized);
                        // print_r($rule_conditions_serialized_arr);
                        if (isset($rule_conditions_serialized_arr->conditions['0']->conditions['0'])) {
                            // 
                            $rule_conditions = $rule_conditions_serialized_arr->conditions['0']->conditions['0'];
                            $rule_conditions_attribute = $rule_conditions->attribute;
                            $rule_conditions_value = $rule_conditions->value;
                            $rule_conditions_skus = explode(',', $rule_conditions_value);
                            $rule_conditions_if_exists = (count(array_intersect($rule_conditions_skus, $all_items_ids))) ? true : false;
                            if ($rule_conditions_if_exists) {
                                // 
                                if (!in_array($rule_promo_skus, $all_items_ids)) {
                                    $freeItemSku []= $rule_promo_skus;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $freeItemSku;
    }

}
