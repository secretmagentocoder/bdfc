<?php

namespace Custom\Api\Controller\Offer;

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
        $this->recursivelyAPICall();
    }

    public function recursivelyAPICall($offset = 0)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $top = 200;
        $skip=$offset;
        //$offer_url = 'http://88.201.70.74:7048/DynamicsNAV71/OData/Company(\'BDFC\')/WebOfferHeader?$format=application/json&$skip='.$skip.'&$top='.$top;
        $offer_url = $host.'/Company(%27'.$company.'%27)/WebOfferHeader?$format=application/json&$top='. $top .'&$skip='. $skip;
        $curl2 = curl_init();
        curl_setopt_array($curl2, array(
            CURLOPT_URL => $offer_url,
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
        $response_data_offer = curl_exec($curl2);
        curl_close($curl2);
        echo '<pre>';
        $response_array_offer = json_decode($response_data_offer, TRUE);
        // print_r($response_array_offer['value'][0]);die;
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('salesrule');
        $query = "Select `rule_id`,`description` FROM " . $table;
        $salesruletable = $connection->fetchAll($query);
        print_r($salesruletable);
        echo "<pre>";
        if (!empty($response_array_offer['value'])) {
            foreach ($response_array_offer['value'] as $offer) {
                $offerNo = $offer['No'];
                $description = $offer['Description'];
                $discountType = $offer['Discount_Type'];
                $linesTrigger = $offer['No_of_Lines_to_Trigger'];
                $linesGroups = $offer['No_of_Line_Groups'];
                $dealPriceValue = $offer['Deal_Price_Value'];
                $discountPercValue = $offer['Discount_Perc_Value']; //Discount %
                $priority = $offer['Priority'];
                $status = $offer['Status'];
                $startDate = $offer['Starting_Date'];
                $endDate = $offer['Ending_Date'];
                $priceGroup = $offer['Price_Group'];
                $discPerc = $offer['Disc_Perc_of_Least_Expensive'];
                // $couponCode = $offer['Coupon_Code'];
                foreach ($salesruletable as  $val) {
                    if (str_contains($val['description'], $offerNo)) {
                        $this->deleteRule($val['rule_id'], $status);
                    }
                }
                if (!empty($offerNo) && $priority != 0) {
                    $this->createDiscountPercentageOffer($offerNo, $description, $discountType, $priceGroup, $linesTrigger, $linesGroups, $dealPriceValue, $discountPercValue, $priority, $status, $startDate, $endDate, $discPerc);
                }
            }
        }

        if (empty($response_array_offer['value']) || count($response_array_offer['value']) >= $top) {
            $newOffset = $offset + $top;
            $this->recursivelyAPICall($newOffset);
        }
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
        $lineBsku="";
        $productSku = "";
        $divisionSku = "";
        $brandSku = '';
        $subBrandSku = '';
        $brandOwnerSku = '';
        $categorySku = '';
        $discount_percentage = 0;
        $steps = [];
        $skus_array = [];
        $lineAskus_array = [];
        $lineBskus_array = [];
        $productSkusArray = [];
        $divisionSkusArray = [];
        $brandSkusArray = [];
        $subBrandSkusArray = [];
        $brandOwnerSkusArray = [];
        $categorySkusArray = [];
        $offerTypeLine = [];
        $discountPercentages = [];
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
            print_r($storeId);
            $offerType = "";
            foreach($response_array_line['value'] as $key => $value){
                $discType = $value['Disc_Type'];
                $discAmount = $value['Deal_Price_Disc_Perc'];
                $lineGroup = $value['Line_Group'];
                if ($discountType == 'Line spec.') {
                    if(preg_match('~[0-9]+~', $lineGroup)){
                        $quantity = $value['NoOfItem'];
                        $discountPercentages[] = $discAmount;
                        if($discType == "Disc. %"){
                            $offerTypeLine[] = 'group_n_percent_discount';
                        }else{
                            $offerTypeLine[] = 'group_nth';
                        }
                        $steps[] = $quantity;
                        if($value['Type'] == "Item Category"){
                            $categorySku.=$value['No'].", ";
                            $categorySkusArray[] = $value["No"];
                        }
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
                        $sku.=$value['No'].", ";
                        $skus_array[] = $value["No"];
                        $lineAskus_array[] = $value["No"];
                        if($lineGroup == "B"){
                            if($discType == "Disc. %"){
                                $offerType = 'buy_x_get_n_percent_discount';
                            }else{
                                $offerType = 'buy_x_get_n_fixed_price';
                            }
                            $discount_percentage = $discAmount;
                            $qty = $lineGroup;
                            $lineBsku=$value['No'];
                        }
                    }
                }else{
                    $sku.=$value['No'].", ";
                    $skus_array[] = $value["No"];
                }
                echo "<pre>";
                echo "loop close";
            }

            $step_qty = (int) $linesTrigger;
            $skus = rtrim($sku, ', ');
            $salesRule = $this->objectManager->create('Magento\SalesRule\Model\Rule');
            if ( ($discountType == 'Line spec.') && ($qty == 'B') ) {
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
                $salesRule->setProductIds(null);
                $salesRule->setSortOrder($priority);
                $salesRule->setSimpleAction($offerType);
                $salesRule->setDiscountAmount($discount_percentage);
                $salesRule->setDiscountQty();
                $salesRule->setDiscountStep($step_qty);
                $salesRule->setWkrulesruleNqty('1');
                $salesRule->setPromoSkus($lineBsku);
                $salesRule->setSimpleFreeShipping('0');
                $salesRule->setApplyToShipping('0');
                $salesRule->setwkrulesrule('0');
                $salesRule->setwkrulesruleNqty('0');
                $salesRule->setwkrulesruleSkipRule('0');
                $salesRule->setmaxDiscount('');
                $salesRule->setpromoCats('');
                $salesRule->setnThreshold('');
                $salesRule->setTimesUsed('0');
                $salesRule->setIsRss('0');
                $salesRule->setWebsiteIds($storeId);
                $salesRule->setCouponType();
                $salesRule->setCouponCode();
                $salesRule->setUsesPerCoupon(null);
                $salesRule->setStopRulesProcessing(1);
                $item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                                                  ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                                                  ->setValue(1) // 1 == FOUND
                                                  ->setAggregator('all'); // match ALL conditions
                $salesRule->getConditions()->addCondition($item_found);
                if (count($lineAskus_array) > 1) {
                    $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                   ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                   ->setData('attribute', 'sku')
                                                   ->setData('operator', '()')
                                                   ->setValue($skus);
                    $salesRule->getActions()->addCondition($actions);
                    $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                      ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                      ->setData('attribute', 'sku')
                                                      ->setData('operator', '()')
                                                      ->setValue($skus);
                    $item_found->addCondition($conditions);
                    if ($step_qty > 1) {
                        $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                             ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                             ->setData('attribute', 'quote_item_qty')
                                                             ->setData('operator', '>=')
                                                             ->setValue($step_qty);
                        $item_found->addCondition($qty_condition);
                    }
                } elseif (count($lineAskus_array) == 1) {
                    $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                   ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                   ->setData('attribute', 'sku')
                                                   ->setData('operator', '==')
                                                   ->setValue($skus);
                    $salesRule->getActions()->addCondition($actions);
                    $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                      ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                      ->setData('attribute', 'sku')
                                                      ->setData('operator', '==')
                                                      ->setValue($skus);
                    $item_found->addCondition($conditions);
                    if ($step_qty > 1) {
                        $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                             ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                             ->setData('attribute', 'quote_item_qty')
                                                             ->setData('operator', '>=')
                                                             ->setValue($step_qty);
                        $item_found->addCondition($qty_condition);
                    }
                }
                try {
                    $salesRule->save();
                    $rule_id = (int) $salesRule->getRuleId();
                    echo 'Rule is created with rule ID : ' . $rule_id . '<br/>';
                } catch (Exception $e) {
                    echo $e;
                }
            }
            echo "<pre>";
            echo "Line Group spec.";
            if( ($discountType == 'Line spec.') && ($qty != 'B') ){
                if($steps){
                    $stepQty = $steps[0];
                    $categorySkus = rtrim($categorySku, ', ');
                    $brandSkus = rtrim($brandSku, ', ');
                    $subBrandSkus = rtrim($subBrandSku, ', ');
                    $brandOwnerSkus = rtrim($brandOwnerSku, ', ');
                    $productSkus = rtrim($productSku, ', ');
                    $divisionSkus = rtrim($divisionSku, ', ');
                    if($offerTypeLine){
                        $offerType = $offerTypeLine[0];
                    }
                    if($discountPercentages){
                        $discount_percentage = $discountPercentages[0];
                    }
                    if ($stepQty) {
                        $discount_percentage = $discount_percentage;
                        $quantity = $stepQty;
                        // echo "<pre>";
                        // echo "quantity";
                        // print_r($quantity);
                        // die;
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
                        $salesRule->setStopRulesProcessing(1);

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

                        if(count($categorySkusArray) >= 1){
                            $category_item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                                                                       ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                                                                       ->setValue(1) // 1 == FOUND
                                                                       ->setAggregator('all'); // match ALL conditions
                            $all_condition_data->addCondition($category_item_found);
                            if (count($categorySkusArray) > 1) {
                                $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                               ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                               ->setData('attribute', 'item_category')
                                                               ->setData('operator', '()')
                                                               ->setValue($categorySkus);
                                $all_action_data->addCondition($actions);
                                // $salesRule->getActions()->addCondition($actions);
                                $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                                  ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                                  ->setData('attribute', 'item_category')
                                                                  ->setData('operator', '()')
                                                                  ->setValue($categorySkus);
                                $category_item_found->addCondition($conditions);
                                if ($quantity > 1) {
                                    $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                                         ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                                         ->setData('attribute', 'quote_item_qty')
                                                                         ->setData('operator', '>=')
                                                                         ->setValue($quantity);
                                    $category_item_found->addCondition($qty_condition);
                                }
                            } elseif (count($categorySkusArray) == 1) {
                                $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                               ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                               ->setData('attribute', 'item_category')
                                                               ->setData('operator', '==')
                                                               ->setValue($categorySkus);
                                $all_action_data->addCondition($actions);
                                $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                                  ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                                  ->setData('attribute', 'item_category')
                                                                  ->setData('operator', '==')
                                                                  ->setValue($categorySkus);
                                $category_item_found->addCondition($conditions);
                                if ($quantity > 1) {
                                    $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                                         ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                                         ->setData('attribute', 'quote_item_qty')
                                                                         ->setData('operator', '>=')
                                                                         ->setValue($quantity);
                                    $category_item_found->addCondition($qty_condition);
                                }
                            }
                        }

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
            echo "<pre>";
            echo "Line spec.";
            $step_qty = (int) $linesTrigger;
            $offerType = 'group_n_percent_discount';
            if ($discountType == 'Least Expensive') {
                print_r($description);
                echo "<pre>";
                // $offerType = 'Percent Discount: Each 5 items with 15% off';
                $offerType = 'group_n_percent_discount';
                $discount_percentage = ( (int) $linesGroups / (int) $linesTrigger ) * 100;
            }
            if ($discountType == 'Discount %') {
                print_r($description);
                echo "<pre>";
                // $offerType = 'Percent Discount: Each 5 items with 15% off';
                $offerType = 'group_n_percent_discount';
                $discount_percentage = $discountPercValue;
            }
            if ($discountType == 'Deal Price') {
                print_r($description);
                echo "<pre>";
                // $offerType = 'Fixed Price: Each 5 items for $50';
                $offerType = 'group_nth';
                $discount_percentage = $dealPriceValue;
            }
            $salesRule = $this->objectManager->create('Magento\SalesRule\Model\Rule');
            if ( ($discountType == 'Least Expensive') || ($discountType == 'Discount %') || ($discountType == 'Deal Price') ){
                echo "<pre>";
                // print_r($storeId[0]);
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
                $salesRule->setProductIds(null);
                $salesRule->setSortOrder($priority);
                $salesRule->setSimpleAction($offerType);
                $salesRule->setDiscountAmount($discount_percentage);
                $salesRule->setDiscountQty();
                $salesRule->setDiscountStep($step_qty);
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
                $salesRule->setStopRulesProcessing(1);
                $item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                                                  ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                                                  ->setValue(1) // 1 == FOUND
                                                  ->setAggregator('all'); // match ALL conditions
                $salesRule->getConditions()->addCondition($item_found);
                if (count($skus_array) > 1) {
                    $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                   ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                   ->setData('attribute', 'sku')
                                                   ->setData('operator', '()')
                                                   ->setValue($skus);
                    $salesRule->getActions()->addCondition($actions);
                    $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                      ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                      ->setData('attribute', 'sku')
                                                      ->setData('operator', '()')
                                                      ->setValue($skus);
                    $item_found->addCondition($conditions);
                    if ($step_qty > 1) {
                        $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                             ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                             ->setData('attribute', 'quote_item_qty')
                                                             ->setData('operator', '>=')
                                                             ->setValue($step_qty);
                        $item_found->addCondition($qty_condition);
                    }
                } elseif (count($skus_array) == 1) {
                    $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                   ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                   ->setData('attribute', 'sku')
                                                   ->setData('operator', '==')
                                                   ->setValue($skus);
                    $salesRule->getActions()->addCondition($actions);
                    $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                      ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                      ->setData('attribute', 'sku')
                                                      ->setData('operator', '==')
                                                      ->setValue($skus);
                    $item_found->addCondition($conditions);
                    if ($step_qty > 1) {
                        $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                                                             ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                                                             ->setData('attribute', 'quote_item_qty')
                                                             ->setData('operator', '>=')
                                                             ->setValue($step_qty);
                        $item_found->addCondition($qty_condition);
                    }
                }
                try {
                    $salesRule->save();
                    $rule_id = (int) $salesRule->getRuleId();
                    echo 'Rule is created with rule ID : ' . $rule_id . '<br/>';
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
