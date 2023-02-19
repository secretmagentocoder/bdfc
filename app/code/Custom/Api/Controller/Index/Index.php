<?php

namespace Custom\Api\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;

	protected $resourceConnection;

    protected $eavSetupFactory;


	public function __construct(
        Context $context, 
        JsonFactory $resultJsonFactory, 
        ResourceConnection $resourceConnection, 
        // EavSetupFactory $eavSetupFactory,
        array $data = array()
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        // $this->eavSetupFactory = $eavSetupFactory;
        parent::__construct($context);
    }

    public function getOfferIfExists($all_items_ids)
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
                    $rule_wkrulesrule_nqty = $rule->getWkrulesruleNqty();
                    $rule_discount_step = $rule->getDiscountStep();
                    $rule_promo_skus = $rule->getPromoSkus();
                    $rule_conditions_serialized = $rule->getConditionsSerialized();

                    if (count($all_items_ids) >= $rule_discount_step) {
                        $rule_conditions_serialized_arr = json_decode($rule_conditions_serialized);
                        // print_r($rule_conditions_serialized_arr);
                        if (isset($rule_conditions_serialized_arr->conditions['0']->conditions['0'])) {
                            $rule_conditions = $rule_conditions_serialized_arr->conditions['0']->conditions['0'];
                            echo $rule_conditions_attribute = $rule_conditions->attribute;
                            echo $rule_conditions_value = $rule_conditions->value;
                            $rule_conditions_skus = explode(',', $rule_conditions_value);
                            $rule_conditions_if_exists = (count(array_intersect($rule_conditions_skus, $all_items_ids))) ? true : false;
                            echo "<br>";
                            echo "Count: ";
                            print_r($rule_conditions_if_exists);
                            echo "<br>";
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
        
        echo "<br> freeItemSku: ";
        print_r($freeItemSku);

    }

    /*public function execute()
    {
        echo '<pre>';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $currentWebsiteId = $_storeManager->getStore()->getWebsiteId();

        $rules = $objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules = $rules->getCollection()->addIsActiveFilter()->addWebsiteFilter($currentWebsiteId)->addFieldToFilter('simple_action', 'buy_x_get_n_percent_discount');
        foreach ($rules as $rule) {
            $rule_id = $rule->getId();
            if ($rule_id == '4294967295') {
                echo $rule_name = $rule->getName();
                $rule_conditions_serialized = $rule->getConditionsSerialized();
                $rule_actions_serialized = $rule->getActionsSerialized();
                print_r($rule_conditions_serialized);
                $rule_conditions_serialized_arr = json_decode($rule_conditions_serialized);
                print_r($rule_conditions_serialized_arr);
                print_r($rule_actions_serialized);
                $rule_actions_serialized_arr = json_decode($rule_actions_serialized);
                print_r($rule_actions_serialized_arr);
            }
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        echo "===========================================================================================";
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        $items = $cart->getQuote()->getAllItems();
        
        echo "<pre>";
        $all_items_ids = [];
        foreach($items as $item) {
            $product_id = $item->getProductId();
            $product_sku = $item->getSku();

            $all_items_ids [$product_id]= $product_sku;
        }

        print_r($all_items_ids);
        if (!empty($all_items_ids)) {
            $getOfferIfExists = $this->getOfferIfExists($all_items_ids);
            print_r($getOfferIfExists);
        }

        echo "</pre>";
        echo "===========================================================================================";
    }*/


    public function getExciseDutyVatPercent($product_id, $store_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store_id = $storeManager->getStore()->getId();
        $taxCalculation = $objectManager->get(\Magento\Tax\Model\Calculation::class);
        $productModel = $objectManager->create('Magento\Catalog\Model\Product');

        $vat_percent_for_excise_duty = 0;
        if (!empty($product_id) && !empty($store_id)) {
            $product = $productModel->setStoreId($store_id)->load($product_id);
            $product_id = $product->getId();
            $product_tax_class_id = $product->getTaxClassId();

            // $defaultCustomerTaxClassId = $this->scopeConfig->getValue('tax/classes/default_customer_tax_class');
            $store = $storeManager->getStore(); 
            $request = $taxCalculation->getRateRequest(null, null, null, $store);
            $tax_rate = $taxCalculation->getRate($request->setProductClassId($product_tax_class_id));

            $vat_percent_for_excise_duty = $tax_rate;
        }

        if (!is_numeric($vat_percent_for_excise_duty) || empty($vat_percent_for_excise_duty)) {
            $vat_percent_for_excise_duty = 0;
        }

        return $vat_percent_for_excise_duty;
    }

    /*public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store_id = $storeManager->getStore()->getId();

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        // get array of all items what can be display directly
        // $items = $cart->getQuote()->getAllVisibleItems();

        // retrieve quote items array
        $items = $cart->getQuote()->getAllItems();

        $total_excise_duty_vat = 0;
        foreach($items as $item) {
            $product_id = $item->getProductId();
            $product_sku = $item->getSku();
            $product_qty = $item->getQty();
            $product_price = $item->getPrice();

            $productModel = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $productModel->setStoreId($store_id)->load($product_id);
            $product_id = $product->getId();
            $product_excise_duty = $product->getExciseDuty();
            $product_excise_duty_price = $product->getExciseDutyPrice();
            $product_price = $product_excise_duty_price;

            if ($product_excise_duty == true) {
                // $vat_percent_for_excise_duty = 10;
                $vat_percent_for_excise_duty = $this->getExciseDutyVatPercent($product_id, $store_id);
                echo "vat percent:";
                echo "<br>";
                print_r($vat_percent_for_excise_duty);
                echo "<br>";

                $item_total_price = $product_price * $product_qty;
                $item_excise_duty_vat = (($item_total_price * $vat_percent_for_excise_duty) / 100);
                $total_excise_duty_vat += $item_excise_duty_vat;
            }
        }
        if (!is_numeric($total_excise_duty_vat)) {
            $total_excise_duty_vat = 0;
        }
        echo "total_excise_duty_vat:";
        echo "<br>";
        echo $total_excise_duty_vat;

        // return $total_excise_duty_vat;
    }*/

    public function execute()
    {
        echo "string";

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        // $itemsVisible = $cart->getQuote()->getAllVisibleItems();
        // $items = $cart->getQuote()->getAllItems();
        $session = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote_repository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        $qid = $session->getQuoteId();
        if (empty($qid)) {
            return 0;
        }
        $quote = $quote_repository->get($qid);      
        $items = $quote->getAllItems();

        $arrival_quantity_on_hand = $cart->getQuote()->getArrivalQuantityOnHand();
        // $arrival_quantity_on_hand = $quote->getArrivalQuantityOnHand();
        $arrival_quantity_on_hand_arr = json_decode($arrival_quantity_on_hand);
        print_r($arrival_quantity_on_hand_arr);

        echo "<pre>";
        $total_custom_fee = 0;
        $custom_duty_arr = [];
        foreach($items as $item) {
            $product_id = $item->getProductId();
            $product_sku = $item->getSku();
            $product_qty = $item->getQty();
            $product_price = $item->getPrice();

            $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
            $product_id = $product->getId();
            $custom_allowence_category = $product->getCustomAllowenceCategory();
            $qty_per_custom_uom = $product->getQtyPerCustomUom();

            // regular_price
            $product_regular_price = $product->getPriceInfo()->getPrice('regular_price')->getValue();
            $product_final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
            $product_excise_duty = $product->getExciseDuty();
            $product_excise_duty_price = $product->getExciseDutyPrice();
            // $product_price = $product_final_price;
            if ($product_excise_duty == true && is_numeric($product_excise_duty_price)) {
                $product_price = $product_final_price - $product_excise_duty_price;
            }else{
                $product_price = $product_final_price;
            }

            if (!empty($custom_allowence_category) && !empty($qty_per_custom_uom)) {
                $product_qty_per_uom = $qty_per_custom_uom * $product_qty;
                $product_price_per_uom = (1 / $qty_per_custom_uom) * $product_price;
                // $product_price_per_uom = round($product_price_per_uom, '3');

                $product_item_arr = [];
                $product_item_arr ['product_sku']= $product_sku;
                $product_item_arr ['product_price']= $product_price;
                $product_item_arr ['product_qty']= $product_qty;
                $product_item_arr ['product_qty_per_uom']= $product_qty_per_uom;
                $product_item_arr ['product_price_per_uom']= $product_price_per_uom;
                $custom_duty_arr [$custom_allowence_category][]= $product_item_arr;
            }
        }
        echo "<br>";
        echo $total_custom_fee;
        echo "<br>";
        print_r($custom_duty_arr);

        // getCustomArraySort
        $custom_duty_arr = $this->getCustomArraySort($custom_duty_arr);
        echo "<br>";
        echo "getCustomArraySort: ";
        print_r($custom_duty_arr);

        // // getCustomOnHandQtyArraySort
        // $custom_duty_arr = $this->getCustomOnHandQtyArraySort($custom_duty_arr, $arrival_quantity_on_hand_arr);
        // echo "<br>";
        // echo "getCustomArraySort2: ";
        // print_r($custom_duty_arr);

        echo "<br>";
        // getCustomDutyPrice
        $total_custom_fee = $this->getCustomDutyPrice($custom_duty_arr);

        echo "string";
        echo "<br>";
        
        if (!is_numeric($total_custom_fee) || empty($total_custom_fee)) {
            $total_custom_fee = 0;
        }

        echo $total_custom_fee;
        echo "<br>";
	}

    /**
    * Get getCustomArraySort
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomArraySort($custom_duty_arr)
    {
        foreach ($custom_duty_arr as $key => $value) {
            $custom_duty_arr_ = $value;

            usort($custom_duty_arr_, function($a, $b) {
                $retval = $a['product_price_per_uom'] <=> $b['product_price_per_uom'];
                return $retval;
            });

            /*usort($custom_duty_arr_, function($a, $b) {
                $retval = $a['order'] <=> $b['order'];
                if ($retval == 0) {
                    $retval = $a['suborder'] <=> $b['suborder'];
                    if ($retval == 0) {
                        $retval = $a['details']['subsuborder'] <=> $b['details']['subsuborder'];
                    }
                }
                return $retval;
            });*/

            $custom_duty_arr[$key] = $custom_duty_arr_;
        }

        return $custom_duty_arr;
    }

    /**
    * Get getCustomParentArraySort
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomParentArraySort($parent_category_arr)
    {
        $custom_parent_category_arr = [];

        if (!empty($parent_category_arr)) {
            foreach ($parent_category_arr as $key => $parent_category_items) {
                $custom_parent_category_code = $key;
                foreach ($parent_category_items as $key => $custom_duty_items) {
                    $custom_allowence_category_id = $key;
                    $custom_qty_per_uom_total = 0;
                    foreach ($custom_duty_items as $key => $value) {
                        $custom_duty_item = [];
                        $custom_duty_item = $value;

                        $product_qty = $value['product_qty'];
                        $product_price = $value['product_price'];
                        $product_qty_per_uom = $value['product_qty_per_uom'];
                        $product_price_per_uom = $value['product_price_per_uom'];
                        
                        $custom_duty_item['category_code']= $custom_allowence_category_id;
                        $custom_parent_category_arr [$custom_parent_category_code][] = $custom_duty_item;
                    }
                }

            }
        }

        // custom_parent_category_arr
        foreach ($custom_parent_category_arr as $key => $value) {
            $custom_parent_category_items_ = $value;
            if (!isset($value['count'])) {
                usort($custom_parent_category_items_, function($a, $b) {
                    $retval = $a['product_price_per_uom'] <=> $b['product_price_per_uom'];
                    return $retval;
                });

                $custom_parent_category_arr[$key] = $custom_parent_category_items_;
            }
        }

        return $custom_parent_category_arr;
    }

    /**
    * Get getCustomOnHandQtyArraySort
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomOnHandQtyArraySort($custom_duty_arr, $arrival_quantity_on_hand_arr)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        if (!empty($custom_duty_arr)) {
            if (!empty($arrival_quantity_on_hand_arr)) {
                print_r($arrival_quantity_on_hand_arr);
                $on_hand_qty_beer = $arrival_quantity_on_hand_arr[0]->on_hand_qty_beer;
                $on_hand_qty_tobacco = $arrival_quantity_on_hand_arr[0]->on_hand_qty_tobacco;
                $on_hand_qty_flv_tobacco = $arrival_quantity_on_hand_arr[0]->on_hand_qty_flv_tobacco;

                // on_hand_qty_beer
                $category_code = 'BEER';
                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                $results = $connection->fetchRow($query);
                if (!empty($results)) {
                    $category_id = $results['id'];
                    $limit_quantity = $results['limit_quantity'];

                    if (!empty($on_hand_qty_beer) && $on_hand_qty_beer != 0 && isset($custom_duty_arr[$category_id])) {
                        if ($limit_quantity < $on_hand_qty_beer) {
                            $on_hand_quantity_final = $limit_quantity;
                        }else{
                            $on_hand_quantity_final = $on_hand_qty_beer;
                        }
                        $product_qty_per_uom = $custom_duty_arr[$category_id][0]['product_qty_per_uom'];
                        $product_qty_per_uom_ = $product_qty_per_uom + $on_hand_quantity_final;
                        $custom_duty_arr [$category_id][0]['product_qty_per_uom']= $product_qty_per_uom_;
                    }
                }
                // on_hand_qty_tobacco
                $category_code = 'TOBACCO';
                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                $results = $connection->fetchRow($query);
                if (!empty($results)) {
                    $category_id = $results['id'];
                    $limit_quantity = $results['limit_quantity'];

                    if (!empty($on_hand_qty_tobacco) && $on_hand_qty_tobacco != 0 && isset($custom_duty_arr[$category_id])) {
                        if ($limit_quantity < $on_hand_qty_tobacco) {
                            $on_hand_quantity_final = $limit_quantity;
                        }else{
                            $on_hand_quantity_final = $on_hand_qty_tobacco;
                        }
                        $product_qty_per_uom = $custom_duty_arr[$category_id][0]['product_qty_per_uom'];
                        $product_qty_per_uom_ = $product_qty_per_uom + $on_hand_quantity_final;
                        $custom_duty_arr [$category_id][0]['product_qty_per_uom']= $product_qty_per_uom_;
                    }
                }
                // on_hand_qty_flv_tobacco
                $category_code = 'FLV TOBACCO';
                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                $results = $connection->fetchRow($query);
                if (!empty($results)) {
                    $category_id = $results['id'];
                    $limit_quantity = $results['limit_quantity'];

                    if (!empty($on_hand_qty_flv_tobacco) && $on_hand_qty_flv_tobacco != 0 && isset($custom_duty_arr[$category_id])) {
                        if ($limit_quantity < $on_hand_qty_flv_tobacco) {
                            $on_hand_quantity_final = $limit_quantity;
                        }else{
                            $on_hand_quantity_final = $on_hand_qty_flv_tobacco;
                        }
                        $product_qty_per_uom = $custom_duty_arr[$category_id][0]['product_qty_per_uom'];
                        $product_qty_per_uom_ = $product_qty_per_uom + $on_hand_quantity_final;
                        $custom_duty_arr [$category_id][0]['product_qty_per_uom']= $product_qty_per_uom_;
                    }
                }
            }
        }

        return $custom_duty_arr;
    }

    /**
    * Get getCustomParentOnHandQtyArraySort
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomParentOnHandQtyArraySort($custom_parent_category_arr, $arrival_quantity_on_hand_arr)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        if (!empty($custom_parent_category_arr)) {
            if (!empty($arrival_quantity_on_hand_arr)) {
                $on_hand_qty_spirit_wine = $arrival_quantity_on_hand_arr[0]->on_hand_qty_spirit_wine;

                // on_hand_qty_spirit_wine
                $category_code = 'SPIRIT+WINE';
                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('parent_category_name = ?', $category_code);
                $results = $connection->fetchRow($query);
                if (!empty($results)) {
                    $category_id = $results['id'];
                    $limit_quantity = $results['parent_category_limit_quanity'];

                    if (!empty($on_hand_qty_spirit_wine) && $on_hand_qty_spirit_wine != 0 && isset($custom_parent_category_arr[$category_code])) {
                        if ($limit_quantity < $on_hand_qty_spirit_wine) {
                            $on_hand_quantity_final = $limit_quantity;
                        }else{
                            $on_hand_quantity_final = $on_hand_qty_spirit_wine;
                        }
                        $product_qty_per_uom = $custom_parent_category_arr[$category_code][0]['product_qty_per_uom'];
                        $product_qty_per_uom_ = $product_qty_per_uom + $on_hand_quantity_final;
                        $custom_parent_category_arr [$category_code][0]['product_qty_per_uom']= $product_qty_per_uom_;
                    }
                }
            }
        }

        return $custom_parent_category_arr;
    }

    /**
    * Get getCustomOnHandQty
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomOnHandQty($check_on_hand_qty)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        $arrival_quantity_on_hand = $cart->getQuote()->getArrivalQuantityOnHand();
        $arrival_quantity_on_hand_arr = json_decode($arrival_quantity_on_hand);
        // print_r($arrival_quantity_on_hand_arr);

        $custom_on_hand_qty_arr = [];
        if (!empty($arrival_quantity_on_hand_arr)) {
            foreach ($arrival_quantity_on_hand_arr[0] as $key => $value) {
                if (empty($value)) {
                    $arrival_quantity_on_hand_arr[0]->$key = 0;
                }
            }
            print_r($arrival_quantity_on_hand_arr);

            $on_hand_qty_spirit_wine = $arrival_quantity_on_hand_arr[0]->on_hand_qty_spirit_wine;
            $on_hand_qty_beer = $arrival_quantity_on_hand_arr[0]->on_hand_qty_beer;
            $on_hand_qty_tobacco = $arrival_quantity_on_hand_arr[0]->on_hand_qty_tobacco;
            $on_hand_qty_flv_tobacco = $arrival_quantity_on_hand_arr[0]->on_hand_qty_flv_tobacco;

            $custom_on_hand_qty_arr ['SPIRIT+WINE']= $on_hand_qty_spirit_wine;
            $custom_on_hand_qty_arr ['BEER']= $on_hand_qty_beer;
            $custom_on_hand_qty_arr ['TOBACCO']= $on_hand_qty_tobacco;
            $custom_on_hand_qty_arr ['FLV TOBACCO']= $on_hand_qty_flv_tobacco;
            
        }
        print_r($custom_on_hand_qty_arr);

        $custom_on_hand_qty = 0;
        if (!empty($check_on_hand_qty)) {
            if (isset($custom_on_hand_qty_arr[$check_on_hand_qty])) {
                $custom_on_hand_qty = $custom_on_hand_qty_arr[$check_on_hand_qty];
            }
        }

        return $custom_on_hand_qty;
    }

    /**
    * Get getCustomDutyPrice
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomDutyPrice($custom_duty_arr)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        $arrival_quantity_on_hand = $cart->getQuote()->getArrivalQuantityOnHand();
        $arrival_quantity_on_hand_arr = json_decode($arrival_quantity_on_hand);

        $parent_category_arr = [];
        $total_custom_duty_fee = 0;
        if (!empty($custom_duty_arr)) {
            foreach ($custom_duty_arr as $key => $custom_duty_items) {
                echo $custom_allowence_category_id = $key;
                echo "<br>";
                $product_qty_per_uom_total = 0;
                foreach ($custom_duty_items as $key => $value) {
                    $product_qty = $value['product_qty'];
                    $product_price = $value['product_price'];
                    $product_qty_per_uom = $value['product_qty_per_uom'];
                    $product_price_per_uom = $value['product_price_per_uom'];
                    $product_qty_per_uom_total += $product_qty_per_uom;
                }
                echo $product_qty_per_uom_total;
                echo "<br>";

                $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('id = ?', $custom_allowence_category_id);
                $results = $connection->fetchRow($query);
                print_r($results);

                $item_total_price_uom = 0;
                if (!empty($results)) {
                    $category_code = $results['category_code'];
                    $limit_quantity = $results['limit_quantity'];
                    $custom_calculation_type = $results['custom_calculation_type'];
                    $custom_charge_amount = $results['custom_charge_amount'];
                    $parent_category_name = $results['parent_category_name'];
                    $parent_category_limit_quanity = $results['parent_category_limit_quanity'];

                    // getCustomOnHandQty
                    $check_on_hand_qty = $this->getCustomOnHandQty($category_code);
                    echo "check_on_hand_qty:";
                    echo $check_on_hand_qty;
                    echo "<br>";
                    $limit_quantity_ = 0;
                    if ($check_on_hand_qty > $limit_quantity) {
                        $limit_quantity_ = 0;
                    }else{
                        $limit_quantity_ = $limit_quantity - $check_on_hand_qty;
                    }

                    if ($product_qty_per_uom_total > $limit_quantity_) {
                        $custom_duty_qty_per_uom_total = $product_qty_per_uom_total - $limit_quantity_;

                        $item_price_per_uom_total = 0;
                        foreach ($custom_duty_items as $key => $value) {
                            $custom_duty_item = $value;
                            $product_qty = $value['product_qty'];
                            $product_price = $value['product_price'];
                            $product_qty_per_uom = $value['product_qty_per_uom'];
                            $product_price_per_uom = $value['product_price_per_uom'];

                            if ($custom_duty_qty_per_uom_total > 0) {
                                $item_price_per_uom = 0;
                                $item_qty_per_uom = 0;
                                if ($custom_duty_qty_per_uom_total < $product_qty_per_uom) {
                                    $item_price_per_uom = $product_price_per_uom * $custom_duty_qty_per_uom_total;
                                    $item_qty_per_uom = $custom_duty_qty_per_uom_total;
                                }else{
                                    $item_price_per_uom = $product_price_per_uom * $product_qty_per_uom;
                                    $item_qty_per_uom = $product_qty_per_uom;
                                }
                                $item_price_per_uom_total += $item_price_per_uom;
                                $custom_duty_qty_per_uom_total = $custom_duty_qty_per_uom_total - $product_qty_per_uom;

                                if (!empty($parent_category_name)) {
                                    $product_qty_per_uom_after = $product_qty_per_uom - $item_qty_per_uom;
                                    $custom_duty_item ['product_qty_per_uom']= $product_qty_per_uom_after;
                                    print_r("custom_duty_item");
                                    print_r($custom_duty_item);
                                    $parent_category_arr [$parent_category_name][$category_code][]= $custom_duty_item;
                                }
                            }else{
                                if (!empty($parent_category_name)) {
                                    $parent_category_arr [$parent_category_name][$category_code][]= $custom_duty_item;
                                }

                                break;
                            }
                        }

                        echo $item_price_per_uom_total;
                        echo "<br>";

                        if ($custom_calculation_type == '%') {
                            $item_total_price_uom = (($item_price_per_uom_total * $custom_charge_amount) / 100);
                        }elseif ($custom_calculation_type == 'Amount') {
                            $item_total_price_uom = $custom_charge_amount;
                        }
                    }else{
                        if (!empty($parent_category_name)) {
                            $parent_category_arr [$parent_category_name][$category_code]= $custom_duty_items;
                        }
                    }
                }
                $total_custom_duty_fee += $item_total_price_uom;
                echo $item_total_price_uom;
                echo "<br>";

            }
        }
        echo "<br>";
        print_r($parent_category_arr);

        $custom_parent_category_arr = $this->getCustomParentArraySort($parent_category_arr);
        echo "<br>";
        echo "custom_parent_category_arr: ";
        print_r($custom_parent_category_arr);

        // $custom_parent_category_arr = $this->getCustomParentOnHandQtyArraySort($custom_parent_category_arr, $arrival_quantity_on_hand_arr);
        // echo "<br>";
        // echo "custom_parent_category_arr2: ";
        // print_r($custom_parent_category_arr);


        echo "<br>";
        echo $total_custom_duty_fee;
        echo "<br>";
        echo "=============================================";
        echo "<br>";
        // getCustomParentDutyPrice
        $total_custom_fee = $this->getCustomParentDutyPrice($custom_parent_category_arr);
        $total_custom_duty_fee += $total_custom_fee;
        echo "=============================================";
        echo "<br>";

        echo $total_custom_duty_fee;
        // $total_custom_duty_fee = round($total_custom_duty_fee, '3');

        echo "<br>";

        if (!is_numeric($total_custom_duty_fee) || empty($total_custom_duty_fee)) {
            $total_custom_duty_fee = 0;
        }

        return $total_custom_duty_fee;
    }

    /**
    * Get getCustomParentDutyPrice
    *
    * @return \Magento\Framework\Phrase
    */
    public function getCustomParentDutyPrice($parent_category_arr)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        
        $total_custom_duty_fee = 0;
        $parent_allowence_category_code = '';
        if (!empty($parent_category_arr)) {
            foreach ($parent_category_arr as $key => $parent_category_items) {
                $parent_allowence_category_code = $key;
                $total_custom_parent_fee = 0;
                $parent_qty_per_uom_total = 0;
                $parent_duty_qty_per_uom_total = 0;

                // 
                foreach ($parent_category_items as $key => $value) {
                    $product_qty = $value['product_qty'];
                    $product_price = $value['product_price'];
                    $product_qty_per_uom = $value['product_qty_per_uom'];
                    $product_price_per_uom = $value['product_price_per_uom'];
                    $parent_qty_per_uom_total += $product_qty_per_uom;
                }
                echo $parent_qty_per_uom_total;
                echo "<br>";
                echo $parent_allowence_category_code;
                echo "<br>";

                if (!empty($parent_allowence_category_code)) {
                    // web_custom_allowence_category
                    $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('parent_category_name = ?', $parent_allowence_category_code);
                    $results = $connection->fetchRow($query);
                    print_r($results);

                    $item_total_price_uom = 0;
                    if (!empty($results)) {
                        $custom_calculation_type = $results['custom_calculation_type'];
                        $custom_charge_amount = $results['custom_charge_amount'];
                        $parent_category_name = $results['parent_category_name'];
                        $parent_category_limit_quanity = $results['parent_category_limit_quanity'];

                        // getCustomOnHandQty
                        $check_on_hand_qty = $this->getCustomOnHandQty($parent_category_name);
                        echo "check_on_hand_qty:";
                        echo $check_on_hand_qty;
                        echo "<br>";
                        $parent_category_limit_quanity_ = 0;
                        if ($check_on_hand_qty > $parent_category_limit_quanity) {
                            $parent_category_limit_quanity_ = 0;
                        }else{
                            $parent_category_limit_quanity_ = $parent_category_limit_quanity - $check_on_hand_qty;
                        }

                        if ($parent_qty_per_uom_total > $parent_category_limit_quanity_) {
                            
                            $parent_duty_qty_per_uom_total = $parent_qty_per_uom_total - $parent_category_limit_quanity_;

                            $item_price_per_uom_total = 0;
                            foreach ($parent_category_items as $key => $value) {
                                $product_qty = $value['product_qty'];
                                $product_price = $value['product_price'];
                                $product_qty_per_uom = $value['product_qty_per_uom'];
                                $product_price_per_uom = $value['product_price_per_uom'];
                                echo $category_code = $value['category_code'];

                                if ($parent_duty_qty_per_uom_total > 0) {
                                    $item_price_per_uom = 0;
                                    if ($parent_duty_qty_per_uom_total < $product_qty_per_uom) {
                                        $item_price_per_uom = $product_price_per_uom * $parent_duty_qty_per_uom_total;
                                    }else{
                                        $item_price_per_uom = $product_price_per_uom * $product_qty_per_uom;
                                    }
                                    $parent_duty_qty_per_uom_total = $parent_duty_qty_per_uom_total - $product_qty_per_uom;

                                    // web_custom_allowence_category
                                    $custom_query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                                    $custom_results = $connection->fetchRow($custom_query);
                                    // print_r($custom_results);
                                    if (!empty($custom_results)) {
                                        $custom_calculation_type = $custom_results['custom_calculation_type'];
                                        $custom_charge_amount = $custom_results['custom_charge_amount'];

                                        if ($custom_calculation_type == '%') {
                                            $item_total_price_uom = (($item_price_per_uom * $custom_charge_amount) / 100);
                                        }elseif ($custom_calculation_type == 'Amount') {
                                            $item_total_price_uom = $custom_charge_amount;
                                        }
                                    }

                                    $total_custom_parent_fee += $item_total_price_uom;
                                }else{
                                    break;
                                }
                            }
                        }
                    }

                    echo $total_custom_duty_fee += $total_custom_parent_fee;
                    echo "<br>";
                }
            }
        }
        echo "<br>";
        echo $total_custom_duty_fee;
        // $total_custom_duty_fee = round($total_custom_duty_fee, '3');
        echo "<br>";

        if (!is_numeric($total_custom_duty_fee) || empty($total_custom_duty_fee)) {
            $total_custom_duty_fee = 0;
        }

        return $total_custom_duty_fee;
    }
    
}