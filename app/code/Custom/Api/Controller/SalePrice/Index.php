<?php

namespace Custom\Api\Controller\SalePrice;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    protected $eavSetupFactory;

    private $navConfigProvider;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->recursivelyAPICall();
        // reindexAll
        $this->reindexAll();

        // // flushCache
        // $this->flushCache();
    }

    public function recursivelyAPICall($offset = 0)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $response = array();
        $top = 10;
        $skip = $offset;
        // $productSku = "409387";
        // WebCategoryLink
        $item_sku = @$_GET['item_sku'];
        if (isset($item_sku) && !empty($item_sku)) {
            $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json&$filter=No%20eq%20%27'.$item_sku.'%27&$top='.$top.'&$skip='.$skip;
        }else{
            $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json&$top='.$top.'&$skip='.$skip;
        }
        // $attribute_url = 'http://88.201.70.74:7048/DynamicsNAV71/OData/Company(\'BDFC\')/WebCategoryLink?$format=application/json&$filter=No%20eq%20%27' . $productSku . '%27';
        // $attribute_url = 'http://88.201.70.74:7048/DynamicsNAV71/OData/Company(\'BDFC\')/WebCategoryLink?$format=application/json&$skip=' . $skip . '&$top=' . $top;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $attribute_url,
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
        $response_data = curl_exec($curl);
        curl_close($curl);
        $productResults = json_decode($response_data, TRUE);
        echo '<pre>';
        // print_r($productResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName3 = $resource->getTableName('catalog_product_entity');
        $getProductLists = [];
        if (!empty($productResults['value'])) {
            foreach ($productResults['value'] as $key => $value) {
                $product = $objectManager->create('Magento\Catalog\Model\Product');
                // $productSku = $productResults['value'][0]['No'];
                // $name = $productResults['value'][0]['Description'];
                // $to = [" - ", " / ", "/", " & ", ' ', "'"];
                // $from = ['-', "-", '-', '-', '-', ""];
                // $urlKey = str_replace($to, $from, strtolower($productResults['value'][0]['Description'])) . '-' . $productResults['value'][0]['No'];
                // $storeIds = $productResults['value'][0]['Web_Store_Code'];
                // $startDate = $productResults['value'][0]['Start_Date'];
                // $endDate = $productResults['value'][0]['End_Date'];
                $productSku = $value['No'];
                $name = $value['Description'];
                $to = [" - ", " / ", "/", " & ", ' ', "'"];
                $from = ['-', "-", '-', '-', '-', ""];
                $urlKey = str_replace($to, $from, strtolower($value['Description'])) . '-' . $value['No'];
                $storeIds = $value['Web_Store_Code'];
                $startDate = $value['Start_Date'];
                $endDate = $value['End_Date'];
                if ($product->getIdBySku($productSku)) {
                    $this->updatePrice($productSku, $name, $urlKey, $startDate, $endDate, $storeIds);
                }
            }
        }
        if (count($productResults['value']) >= $top) {
            $newOffset = $offset + $top;
            $this->recursivelyAPICall($newOffset);
        }
    }

    public function updatePrice($productSku, $name, $urlKey, $startDate, $endDate, $storeIds)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $response = array();
        $curl = curl_init();
        $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebSalesPrice?$format=application/json&$filter=Item_No%20eq%20%27' . $productSku . '%27';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $attribute_url,
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
        $response_data = curl_exec($curl);
        $priceResults = json_decode($response_data, TRUE);
        // echo "<pre>";
        // print_r($priceResults);
        $getPriceDataLists = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName = $resource->getTableName('web_store_price_group');
        $tableName1 = $resource->getTableName('web_store_num');
        $tableName2 = $resource->getTableName('catalog_category_entity');
        $tableName3 = $resource->getTableName('catalog_product_entity');
        foreach ($priceResults['value'] as $key => $value) {
            $priceSku = $value['Item_No'];
            $storeName = $value['Sales_Code'];
            $fields = [];
            $fields['price_sku'] = $value['Item_No'];
            $fields['Sales_Code'] = $value['Sales_Code'];
            $fields['priceStartingDate'] = $value['Starting_Date'];
            $fields['Unit_Price'] = $value['Unit_Price'];
            $getPriceDataLists[] = $fields;
        }
        // echo "<pre>";
        // print_r($getPriceDataLists);
        if ($getPriceDataLists) {
            $getStores = explode("|", $storeIds);
            $dataList = [];
            $allPriorities = [];
            $getPriority = [];
            $storePriority = [];
            $salesCode = [];
            $storeDayDifference = [];
            foreach($getStores as $setStore){
                unset($daysDifference);
                $allPriorities = [];
                $getPriceDatas = [];
                foreach ($getPriceDataLists as $getPriceData) {
                    $Sales_Code = $getPriceData['Sales_Code'];
                    $getPriceWithPriority = "Select store_id, price_group_code, priority FROM " . $tableName . " where store_id = '$setStore' && price_group_code = '$Sales_Code' LIMIT 1";
                    $result = $connection->fetchAll($getPriceWithPriority);
                    $fields = [];
                    $fields['price_sku'] = $getPriceData['price_sku'];
                    $fields['Sales_Code'] = $getPriceData['Sales_Code'];
                    $fields['priceStartingDate'] = $getPriceData['priceStartingDate'];
                    $fields['Unit_Price'] = $getPriceData['Unit_Price'];
                    if($result){
                        $fields['getPriorityData'] = $result[0];
                    }
                    $getPriceDatas[] = $fields;
                }
                echo "<pre>";
                print_r($getPriceDatas);
                foreach ($getPriceDatas as $getPriceData) {
                    $unitPrice = $getPriceData['Unit_Price'];
                    $Sales_Code = $getPriceData['Sales_Code'];
                    $priceStartingDate = $getPriceData['priceStartingDate'];
                    if(isset($getPriceData['getPriorityData'])){
                        $store_id = $getPriceData['getPriorityData']['store_id'];
                        if($setStore){
                            $priceGroupCode = $getPriceData['getPriorityData']['price_group_code'];
                            $gettingStore = "Select magento_store_id, price_group_code, priority FROM " . $tableName . " where store_id = '$setStore' && price_group_code = '$priceGroupCode'";
                            $result = $connection->fetchAll($gettingStore);
                            $priceStartDate = explode("T", $priceStartingDate)[0];
                            $todayDate = $this->timezone->date()->format('Y-m-d');
                            $dateTimeObject1 = date_create($priceStartDate);
                            $dateTimeObject2 = date_create($todayDate);
                            $difference = date_diff($dateTimeObject1, $dateTimeObject2);
                            if($result){
                                $allPriorities[] = $result[0]['priority'];
                                $salesCode[$Sales_Code] = $result[0]['priority'];
                                $daysDifference[] = $difference->format('%a');
                                $getPriority[] = $getPriceData['getPriorityData']['priority'];
                            }
                        }
                    }
                }
                // echo "<pre>";
                // print_r($salesCode);
                $filterPriorities = [];
                if($allPriorities){
                    $count= count(array_keys($allPriorities, max($allPriorities)));
                    $unique = array_unique($allPriorities);
                    if(count($unique) == 1){
                        $daysDiff = min($daysDifference);
                        $filterPriorities[] = $allPriorities;
                        // echo "<pre>";
                        // print_r($filterPriorities);
                    }elseif(count($unique) >= 2){
                        $priority1 = [];
                        $daysDifference = [];
                        foreach ($allPriorities as $key=>$pro){
                            if(max($allPriorities) == $pro){
                                foreach ($getPriceDatas as $getPriceData) {
                                    if(isset($getPriceData['getPriorityData'])){
                                        $priority = $getPriceData['getPriorityData']['priority'];
                                        $priceStartingDate = $getPriceData['priceStartingDate'];
                                        if($pro == $priority){
                                            $priceStartDate = explode("T", $priceStartingDate)[0];
                                            $todayDate = $this->timezone->date()->format('Y-m-d');
                                            $dateTimeObject1 = date_create($priceStartDate);
                                            $dateTimeObject2 = date_create($todayDate);
                                            $difference = date_diff($dateTimeObject1, $dateTimeObject2);
                                            $daysDifference[] = $difference->format('%a');
                                        }
                                    }
                                }
                                $priority1[] = $pro;
                            }
                        }
                        echo "<pre>";
                        $daysDiff = min($daysDifference);
                        // print_r($daysDiff);
                        $filterPriorities[] = $priority1;
                        // print_r($filterPriorities);
                    }
                    echo "<pre>";
                    print_r($filterPriorities);
                    if($filterPriorities){
                        foreach ($getPriceDatas as $getPriceData) {
                            $price_sku = $getPriceData['price_sku'];
                            $Sales_Code = $getPriceData['Sales_Code'];
                            $priceStartingDate = $getPriceData['priceStartingDate'];
                            if(isset($getPriceData['getPriorityData'])){
                                $priority = $getPriceData['getPriorityData']['priority'];
                                $filterPriorities = $filterPriorities[0][0];
                                if ($priority == $filterPriorities) {
                                    if ($Sales_Code && $productSku == $price_sku) {
                                        $priceStartDate = explode("T", $priceStartingDate)[0];
                                        $todayDate = $this->timezone->date()->format('Y-m-d');
                                        $dateTimeObject1 = date_create($priceStartDate);
                                        $dateTimeObject2 = date_create($todayDate);
                                        $difference = date_diff($dateTimeObject1, $dateTimeObject2);
                                        $daysDifference = $difference->format('%a');
                                        if ($daysDifference == $daysDiff) {
                                            echo "<pre>";
                                            print_r($setStore);
                                            echo "<pre>";
                                            echo "get difference";
                                            $unitPrice = $getPriceData['Unit_Price'];
                                            $getproduct = "Select entity_id FROM " . $tableName3 . " where sku = $productSku";
                                            $result = $connection->fetchAll($getproduct);
                                            $getproductid = $result[0]['entity_id'];
                                            $product = $objectManager->create('Magento\Catalog\Model\Product');
                                            $product->load($getproductid);
                                            $startDate = explode("T", $startDate)[0];
                                            $endDate = explode("T", $endDate)[0];
                                            $todayDate = $this->timezone->date()->format('Y-m-d');
                                            try {
                                                echo "<pre>";
                                                print_r($setStore);
                                                $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$setStore'";
                                                $result = $connection->fetchAll($getlastid);
                                                $stores = $result[0]['store_id'];
                                                if ($startDate == '0001-01-01' && $endDate == '0001-01-01') {
                                                    $this->action->updateAttributes([$getproductid], ['name' => $name, 'price' => $unitPrice, 'unit_price' => $unitPrice, 'status' => 0], $stores);
                                                    $this->action->updateAttributes([$getproductid], ['name' => $name, 'price' => $unitPrice, 'unit_price' => $unitPrice, 'status' => 0], 0);
                                                } elseif (($startDate == '0001-01-01' && $endDate >= $todayDate) || ($startDate <= $todayDate && $endDate == '0001-01-01') || ($startDate <= $todayDate && $endDate >= $todayDate)) {
                                                    $this->action->updateAttributes([$getproductid], ['name' => $name, 'price' => $unitPrice, 'unit_price' => $unitPrice, 'status' => 1], $stores);
                                                    $this->action->updateAttributes([$getproductid], ['name' => $name, 'price' => $unitPrice, 'unit_price' => $unitPrice, 'status' => 1], 0);
                                                } else {
                                                    $this->action->updateAttributes([$getproductid], ['name' => $name, 'price' => $unitPrice, 'unit_price' => $unitPrice, 'status' => 0], $stores);
                                                    $this->action->updateAttributes([$getproductid], ['name' => $name, 'price' => $unitPrice, 'unit_price' => $unitPrice, 'status' => 0], 0);
                                                }
                                                $product->setStockData(array(
                                                    'qty' => 10,
                                                    'is_in_stock' => 1
                                                ));
                                                $product->save();
                                                echo "updated";
                                            } catch (\Exception $e) {
                                                echo $e->getMessage();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // reindexAll
    public function reindexAll()
    {
        // php bin/magento indexer:reindex

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $indexerFactory = $objectManager->get('Magento\Indexer\Model\IndexerFactory');
        $indexerIds = array(
            'catalog_category_product',
            'catalog_product_category',
            'catalog_product_price',
            'catalog_product_attribute',
            'cataloginventory_stock',
            'catalogrule_product',
            'catalogsearch_fulltext',
        );
        foreach ($indexerIds as $indexerId) {
            // echo " create index: ".$indexerId."\n";
            $indexer = $indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }
    }

    // flushCache
    public function flushCache()
    {
        // php bin/magento c:f

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
        $_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
        $types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
        foreach ($types as $type) {
            $_cacheTypeList->cleanType($type);
        }
        foreach ($_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
