<?php

namespace Custom\Api\Controller\TestOffer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action 
{
    protected $resultJsonFactory;
    protected $resourceConnection;
    protected $eavSetupFactory;
    protected $_coupon;
    protected $storeManager;
    protected $objectManager;
    private $navConfigProvider;
    
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectmanager,
        StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Coupon $coupon,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectmanager;
        $this->_coupon = $coupon;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute() 
    {
        // echo "<pre>";
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $_storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        // $currentWebsiteId = $_storeManager->getStore()->getWebsiteId();
        // $rules = $objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        // $rules = $rules->getCollection()->addIsActiveFilter();
        // foreach ($rules as $rule) {
        //     $rule_id = $rule->getId();
        //     if ($rule_id == '59280') {
        //         echo $rule_name = $rule->getName();
        //         $rule_conditions_serialized = $rule->getConditionsSerialized();
        //         $rule_actions_serialized = $rule->getActionsSerialized();
        //         print_r($rule_conditions_serialized);
        //         $rule_conditions_serialized_arr = json_decode($rule_conditions_serialized);
        //         print_r($rule_conditions_serialized_arr);
        //         print_r($rule_actions_serialized);
        //         $rule_actions_serialized_arr = json_decode($rule_actions_serialized);
        //         print_r($rule_actions_serialized_arr);
        //     }
        // }
        $this->recursivelyAPICall(); 
    }
    
    public function recursivelyAPICall($offset = 0)
    {
        // $top = 500;
        // $skip=$offset;
        // $offer_url = 'http://88.201.70.74:7048/DynamicsNAV71/OData/Company(\'BDFC\')/WebOfferHeader?$format=application/json&$skip='.$skip.'&$top='.$top;
        // $curl2 = curl_init();
        // curl_setopt_array($curl2, array(
        //     CURLOPT_URL => $offer_url,
        //     CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
        //     CURLOPT_USERPWD => "Navtest:Bdds$123",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        // ));
        // $response_data_offer = curl_exec($curl2);
        // curl_close($curl2);
        // echo '<pre>';
        // $response_array_offer = json_decode($response_data_offer, TRUE);
        // print_r($response_array_offer['value'][0]);die;
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('salesrule');
        $query = "Select `rule_id`,`description` FROM " . $table;
        $salesruletable = $connection->fetchAll($query);
        // print_r($salesruletable);
        // echo "<pre>";
        // foreach ($response_array_offer['value'] as $offer) {
            $offerNo = 'PROMO-9503';
            $description = "BUY 4 AND MORE GET 20% OFF-(O)";
            $discountType = "Line spec.";
            $linesTrigger = "4";
            $linesGroups = 1;
            $dealPriceValue = "0";
            $discountPercValue = "0"; //Discount %
            $priority = 59280;
            $status = 'Enabled';
            $startDate = "2021-09-01T00:00:00";
            $endDate = "2021-10-04T00:00:00";
            $priceGroup = "ONLINE";
            $discPerc = "100";
            // Disc_Perc_of_Least_Expensive
            // $couponCode = $offer['Coupon_Code'];
            // foreach ($salesruletable as  $val) {
            //     if (str_contains($val['description'], $offerNo)) {
            //         $this->deleteRule($val['rule_id'], $status);
            //     }
            // }
            if (!empty($offerNo) && $priority != 0) {
                $this->createDiscountPercentageOffer($offerNo, $description, $discountType, $priceGroup, $linesTrigger, $linesGroups, $dealPriceValue, $discountPercValue, $priority, $status, $startDate, $endDate, $discPerc);
            }
        // }
        // if (count($response_array_offer['value']) >= $top) {
        //     $newOffset = $offset + $top;
        //     $this->recursivelyAPICall($newOffset);
        // }
    }

    public function createDiscountPercentageOffer($offerNo, $description, $discountType, $priceGroup, $linesTrigger, $linesGroups, $dealPriceValue, $discountPercValue, $priority, $status, $startDate, $endDate, $discPerc) 
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $offers = $host.'/Company('.'\''.$company.'\''.')/WebOfferLine?$format=application/json&$filter=Offer_No%20eq%20%27'.$offerNo.'%27';
        $curl1 = curl_init();
        curl_setopt_array($curl1, array(
            CURLOPT_URL => $offers,
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $user.':'.$password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response_data_line = curl_exec($curl1);
        curl_close($curl1);
        echo '<pre>';
        $response_array_line = json_decode($response_data_line, TRUE);
        if($status == "Enabled"){
            $status = 1;
        }else{
            $status = 0;
        }
        $sku="";
        $qty="";
        $lineAsku="";
        $lineBsku="";
        $productSku = "";
        $divisionSku = "";
        $brandSku = '';
        $subBrandSku = '';
        $brandOwnerSku = '';
        $discount_percentage = 0;
        $skus_array = [];
        $lineAskus_array = [];
        $lineBskus_array = [];
        $productSkusArray = [];
        $divisionSkusArray = [];
        $brandSkusArray = [];
        $subBrandSkusArray = [];
        $brandOwnerSkusArray = [];
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName = $resource->getTableName('web_store_price_group');
        $getPriceWithPriority = "Select magento_store_id FROM " . $tableName . " where price_group_code = '$priceGroup'";
        $results = $connection->fetchAll($getPriceWithPriority);
        if($status == 1){
            if($results){
                $storeId = [];
                foreach($results as $result){
                    $storeId[] = $result['magento_store_id'];
                }
            }else{
                $storeId = array ('2', '3', '4');
            }
            echo "<pre>";
            // print_r($storeId);
            $offerType = "";
            $steps = [];
            foreach($response_array_line['value'] as $key => $value){
                $quantity = $value['NoOfItem']; // when integer 
                $discType = $value['Disc_Type'];
                $discAmount = $value['Deal_Price_Disc_Perc'];
                // $lineSku = $value['No'];
                if ($discountType == 'Line spec.') {
                    if(preg_match('~[0-9]+~', $quantity)){
                        $discount_percentage = $discAmount;
                        if($discType == "Disc. %"){
                            $offerType = 'group_n_percent_discount';
                        }else{
                            $offerType = 'group_nth';
                        }
                        $steps[] = $quantity;
                        if($value['Type'] == "Item"){
                            $sku.=$value['No'].", ";
                            $skus_array[] = $value["No"];
                        }
                        if($value['Type'] == "Brand"){
                            $brandSku.=$value['No'].", ";
                            $brandSkusArray[] = $value["No"];
                        }
                        if($value['Type'] == "Sub-Brand"){
                            $subBrandSku.=$value['No'].", ";
                            $subBrandSkusArray[] = $value["No"];
                        }
                        if($value['Type'] == "Brand Owner"){
                            $brandOwnerSku.=$value['No'].", ";
                            $brandOwnerSkusArray[] = $value["No"];
                        }
                        if($value['Type'] == "Product Group"){
                            $productSku.=$value['No'].", ";
                            $productSkusArray[] = $value["No"];
                        }
                        if($value['Type'] == "Division"){
                            $divisionSku.=$value['No'].", ";
                            $divisionSkusArray[] = $value["No"];
                        }
                    }else{
                        $lineAsku.=$value['No'].", ";
                        $lineAskus_array[] = $value["No"];
                        if($quantity == "B"){
                            if($discType == "Disc. %"){
                                $offerType = 'buy_x_get_n_percent_discount';
                                // $offerType = 'Percent Discount: Buy X get Y Free';
                            }else{
                                $offerType = 'buy_x_get_n_fixed_price';
                                // $offerType = 'Fixed Price: Buy X get Y for $7.45';
                            }
                            $discount_percentage = $discAmount;
                            $qty = $quantity;
                            $lineBsku=$value['No'];
                        }
                    }
                }
            }
            $skus = rtrim($sku, ', ');
            $brandSkus = rtrim($brandSku, ', ');
            $subBrandSkus = rtrim($subBrandSku, ', ');
            $brandOwnerSkus = rtrim($brandOwnerSku, ', ');
            $productSkus = rtrim($productSku, ', ');
            $divisionSkus = rtrim($divisionSku, ', ');
            $quantity = $steps[0];
            $offerType = 'group_n_percent_discount';
            if (($discountType == 'Line spec.') && isset($quantity)) {
                $discount_percentage = $discPerc;
                $salesRule = $this->objectManager->create('Magento\SalesRule\Model\Rule');
                $salesRule->setName($description);
                $salesRule->setDescription($description. ' #' . $offerNo);
                $salesRule->setFromDate($startDate);
                $salesRule->setToDate($endDate);
                $salesRule->setRuleId($priority);
                $salesRule->setUsesPerCustomer('1000');
                $salesRule->setCustomerGroupIds(array('0', '1', '2', '3'));
                $salesRule->setIsActive($status);
                $salesRule->setStopRulesProcessing('0');
                $salesRule->setIsAdvanced('1');
                $salesRule->setSortOrder($priority);
                $salesRule->setSimpleAction($offerType);
                $salesRule->setDiscountAmount($discount_percentage);
                $salesRule->setDiscountStep($quantity);
                $salesRule->setSimpleFreeShipping('0');
                $salesRule->setApplyToShipping('0');
                $salesRule->setwkrulesrule('0');
                $salesRule->setwkrulesruleNqty('0');
                $salesRule->setwkrulesruleSkipRule('0');
                $salesRule->setmaxDiscount('');
                $salesRule->setpromoCats('');
                $salesRule->setpromoSkus('');
                $salesRule->setnThreshold('');
                $salesRule->setTimesUsed('0');
                $salesRule->setIsRss('0');
                $salesRule->setWebsiteIds($storeId);
                $salesRule->setCouponType();
                $salesRule->setCouponCode();
                $salesRule->setUsesPerCoupon(null);

                $all_condition_data = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Combine')
                    ->setType('Magento\SalesRule\Model\Rule\Condition\Combine')
                    ->setValue(1) // 1 == FOUND
                    ->setAggregator('any'); // match ALL conditions
                $salesRule->getConditions()->addCondition($all_condition_data);
                $all_action_data = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
                    ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
                    ->setValue(1) // 1 == FOUND
                    ->setAggregator('any'); // match ALL conditions
                $salesRule->getActions()->addCondition($all_action_data);

                if(count($skus_array) >= 1){
                    $item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setValue(1) // 1 == FOUND
                        ->setAggregator('all'); // match ALL conditions
                    $all_condition_data->addCondition($item_found);
                    if (count($skus_array) > 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sku')
                            ->setData('operator', '()')
                            ->setValue($skus);
                        $all_action_data->addCondition($actions);
                        // $salesRule->getActions()->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sku')
                            ->setData('operator', '()')
                            ->setValue($skus);
                        $item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $item_found->addCondition($qty_condition);
                        }
                    } elseif (count($skus_array) == 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sku')
                            ->setData('operator', '==')
                            ->setValue($skus);
                        $all_action_data->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sku')
                            ->setData('operator', '==')
                            ->setValue($skus);
                        $item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $item_found->addCondition($qty_condition);
                        }
                    }
                }

                if (count($brandOwnerSkusArray) >= 1){     
                    $brand_owner_item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setValue(1) // 1 == FOUND
                        ->setAggregator('all'); // match ALL conditions
                    $all_condition_data->addCondition($brand_owner_item_found);
                    if (count($brandOwnerSkusArray) > 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'brand_owner')
                            ->setData('operator', '()')
                            ->setValue($brandOwnerSkus);
                        $all_action_data->addCondition($actions);
                        // $salesRule->getActions()->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'brand_owner')
                            ->setData('operator', '()')
                            ->setValue($brandOwnerSkus);
                        $brand_owner_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $brand_owner_item_found->addCondition($qty_condition);
                        }
                    } elseif (count($brandOwnerSkusArray) == 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'brand_owner')
                            ->setData('operator', '==')
                            ->setValue($brandOwnerSkus);
                        $all_action_data->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'brand_owner')
                            ->setData('operator', '==')
                            ->setValue($brandOwnerSkus);
                        $brand_owner_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $brand_owner_item_found->addCondition($qty_condition);
                        }
                    }
                }

                if (count($brandSkusArray) >= 1){
                    $brand_item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setValue(1) // 1 == FOUND
                        ->setAggregator('all'); // match ALL conditions
                    $all_condition_data->addCondition($brand_item_found);
                    if (count($brandSkusArray) > 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'brand_code')
                            ->setData('operator', '()')
                            ->setValue($brandSkus);
                        $all_action_data->addCondition($actions);
                        // $salesRule->getActions()->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'brand_code')
                            ->setData('operator', '()')
                            ->setValue($brandSkus);
                        $brand_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $brand_item_found->addCondition($qty_condition);
                        }
                    } elseif (count($brandSkusArray) == 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'brand_code')
                            ->setData('operator', '==')
                            ->setValue($brandSkus);
                        $all_action_data->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'brand_code')
                            ->setData('operator', '==')
                            ->setValue($brandSkus);
                        $brand_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $brand_item_found->addCondition($qty_condition);
                        }
                    }
                }

                if (count($subBrandSkusArray) >= 1){
                    $sub_brand_item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setValue(1) // 1 == FOUND
                        ->setAggregator('all'); // match ALL conditions
                    $all_condition_data->addCondition($sub_brand_item_found);
                    if (count($subBrandSkusArray) > 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sub_brand_code')
                            ->setData('operator', '()')
                            ->setValue($subBrandSkus);
                        $all_action_data->addCondition($actions);
                        // $salesRule->getActions()->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sub_brand_code')
                            ->setData('operator', '()')
                            ->setValue($subBrandSkus);
                        $sub_brand_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $sub_brand_item_found->addCondition($qty_condition);
                        }
                    } elseif (count($subBrandSkusArray) == 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sub_brand_code')
                            ->setData('operator', '==')
                            ->setValue($subBrandSkus);
                        $all_action_data->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sub_brand_code')
                            ->setData('operator', '==')
                            ->setValue($subBrandSkus);
                        $sub_brand_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $sub_brand_item_found->addCondition($qty_condition);
                        }
                    }
                }

                if (count($productSkusArray) >= 1){
                    $product_group_item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setValue(1) // 1 == FOUND
                        ->setAggregator('all'); // match ALL conditions
                    $all_condition_data->addCondition($product_group_item_found);
                    if (count($productSkusArray) > 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'product_group')
                            ->setData('operator', '()')
                            ->setValue($productSkus);
                        $all_action_data->addCondition($actions);
                        // $salesRule->getActions()->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'product_group')
                            ->setData('operator', '()')
                            ->setValue($productSkus);
                        $product_group_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $product_group_item_found->addCondition($qty_condition);
                        }
                    } elseif (count($productSkusArray) == 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'product_group')
                            ->setData('operator', '==')
                            ->setValue($productSkus);
                        $all_action_data->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'product_group')
                            ->setData('operator', '==')
                            ->setValue($productSkus);
                        $product_group_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $product_group_item_found->addCondition($qty_condition);
                        }
                    }
                }

                if (count($divisionSkusArray) >= 1){
                    $division_item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                        ->setValue(1) // 1 == FOUND
                        ->setAggregator('all'); // match ALL conditions
                    $all_condition_data->addCondition($division_item_found);
                    if (count($divisionSkusArray) > 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'division')
                            ->setData('operator', '()')
                            ->setValue($divisionSkus);
                        $all_action_data->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'division')
                            ->setData('operator', '()')
                            ->setValue($divisionSkus);
                        $division_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $division_item_found->addCondition($qty_condition);
                        }
                    } elseif (count($divisionSkusArray) == 1) {
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'division')
                            ->setData('operator', '==')
                            ->setValue($divisionSkus);
                        $all_action_data->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'division')
                            ->setData('operator', '==')
                            ->setValue($divisionSkus);
                        $division_item_found->addCondition($conditions);
                        if ($quantity > 1) {
                            $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                ->setData('attribute', 'quote_item_qty')
                                ->setData('operator', '>=')
                                ->setValue($quantity);
                            $division_item_found->addCondition($qty_condition);
                        }
                    }
                }

                try {
                    $salesRule->save();
                    $rule_id = (int) $salesRule->getRuleId();
                    echo "<pre>";
                    echo 'Line Rule is created with rule ID : ' . $rule_id . '<br/>';
                } catch (Exception $e) {
                    echo $e;
                }
            }
        }
    }

    public function deleteRule($ruleId, $status) {
        if (!empty($rule_id)) {
            $salesRule = $this->objectManager->create('Magento\SalesRule\Api\RuleRepositoryInterface');
            echo $salesRule->deleteById($ruleId);
        }
        if ($status == 'Disabled') {
            $salesRule = $this->objectManager->create('Magento\SalesRule\Api\RuleRepositoryInterface');
            echo $salesRule->deleteById($ruleId);
        }
    }
}
