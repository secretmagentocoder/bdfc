<?php

namespace Custom\Api\Controller\PreviousProduct;

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
        $top = 500;
        $skip=$offset;
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $offer_url = $host.'/Company('.'\''.$company.'\''.')/WebOfferHeader?$format=application/json&$skip='.$skip.'&$top='.$top;
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
        // print_r($salesruletable);
        // echo "<pre>";
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
            $couponCode = $offer['Coupon_Code'];
            foreach ($salesruletable as  $val) {
                if (str_contains($val['description'], $offerNo)) {
                    $this->deleteRule($val['rule_id'], $status);
                }
            }
            if (!empty($offerNo) && $priority != 0) {
                $this->createDiscountPercentageOffer($offerNo, $description, $discountType, $priceGroup, $linesTrigger, $linesGroups, $dealPriceValue, $discountPercValue, $couponCode, $priority, $status, $startDate, $endDate);
            }
        }
        if (count($response_array_offer['value']) >= $top) {
            $newOffset = $offset + $top;
            $this->recursivelyAPICall($newOffset);
        }
    }

    public function createDiscountPercentageOffer($offerNo, $description, $discountType, $priceGroup, $linesTrigger, $linesGroups, $dealPriceValue, $discountPercValue, $couponCode, $priority, $status, $startDate, $endDate) 
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
        $discount_percentage = 0;
        $skus_array = [];
        $lineAskus_array = [];
        $lineBskus_array = [];
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
                $sku.=$value['No'].", ";
                $skus_array[] = $value["No"];
                $quantity = $value['Line_Group']; // when integer 
                $discType = $value['Disc_Type'];
                $discAmount = $value['Deal_Price_Disc_Perc'];
                $lineSku = $value['No'];
                if ($discountType == 'Line spec.') {
                    if(preg_match('~[0-9]+~', $quantity)){
                        $discount_percentage = $discAmount;
                        if($discType == "Disc. %"){
                            // $offerType = 'Percent Discount: Each 5 items with 15% off';
                            $offerType = 'group_n_percent_discount';
                        }else{
                            // $offerType = 'Fixed Price: Each 5 items for $50';
                            $offerType = 'group_nth';
                        }
                        $sku = preg_replace('/[^0-9]/', '', $lineSku); 
                        $getNumericOffer = preg_replace('/[^0-9]/', '', $offerNo); 
                        $setRuleId = $priority+$sku+$getNumericOffer;
                        $salesRule = $this->objectManager->create('Magento\SalesRule\Model\Rule');
                        $salesRule->setName($description);
                        $salesRule->setDescription($description. ' #' . $offerNo);
                        $salesRule->setFromDate($startDate);
                        $salesRule->setToDate($endDate);
                        $salesRule->setRuleId($setRuleId);
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
                        if($couponCode){
                            $salesRule->setCouponType(2);
                            $salesRule->setCouponCode($couponCode);
                        }else{
                            $salesRule->setCouponType();
                            $salesRule->setCouponCode();
                        }
                        $salesRule->setUsesPerCoupon(null);
                        $item_found = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
                            ->setValue(1) // 1 == FOUND
                            ->setAggregator('all'); // match ALL conditions
                        $salesRule->getConditions()->addCondition($item_found);
                        $actions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sku')
                            ->setData('operator', '==')
                            ->setValue($lineSku);
                        $salesRule->getActions()->addCondition($actions);
                        $conditions = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'sku')
                            ->setData('operator', '==')
                            ->setValue($lineSku);
                        $item_found->addCondition($conditions);
                        $qty_condition = $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
                            ->setData('attribute', 'quote_item_qty')
                            ->setData('operator', '>=')
                            ->setValue($quantity);
                        $item_found->addCondition($qty_condition);
                        try {
                            $salesRule->save();
                            $rule_id = (int) $salesRule->getRuleId();
                            echo "<pre>";
                            echo 'Line Rule is created with rule ID : ' . $rule_id . '<br/>';
                        } catch (Exception $e) {
                            echo $e;
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
            $skus = rtrim($lineAsku, ', ');
            $step_qty = (int) $linesTrigger;
            echo "<pre>";
            print_r($discount_percentage);
            echo "<pre>";
            print_r($lineBsku);
            echo "<pre>";
            print_r($offerType);
            echo "<pre>";
            print_r($storeId);
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
                if($couponCode){
                    $salesRule->setCouponType(2);
                    $salesRule->setCouponCode($couponCode);
                }else{
                    $salesRule->setCouponType();
                    $salesRule->setCouponCode();
                }
                $salesRule->setUsesPerCoupon(null);
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
            $skus = rtrim($sku, ', ');
            $step_qty = (int) $linesTrigger;
            if ($discountType == 'Least Expensive') {
                // print_r($description);
                echo "<pre>";
                // $offerType = 'Percent Discount: Each 5 items with 15% off';
                $offerType = 'group_n_percent_discount';
                $discount_percentage = ( (int) $linesGroups / (int) $linesTrigger ) * 100;
            }
            if ($discountType == 'Discount %') {
                // print_r($description);
                echo "<pre>";
                // $offerType = 'Percent Discount: Each 5 items with 15% off';
                $offerType = 'group_n_percent_discount';
                $discount_percentage = $discountPercValue;
            }
            if ($discountType == 'Deal Price') {
                // print_r($description);
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
                if($couponCode){
                    $salesRule->setCouponType(2);
                    $salesRule->setCouponCode($couponCode);
                }else{
                    $salesRule->setCouponType();
                    $salesRule->setCouponCode();
                }
                $salesRule->setUsesPerCoupon(null);
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
