<?php

namespace Custom\Api\Controller\Product;

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
        )
    {
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
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json';
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
        $productResults = json_decode($response_data,TRUE);
        // echo '<pre>';
        // print_r($productResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
                            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
        $tableName = $resource->getTableName('web_store_price_group');
        $tableName1 = $resource->getTableName('web_store_num');
        $tableName2 = $resource->getTableName('catalog_category_entity');
        $tableName3 = $resource->getTableName('catalog_product_entity');
        foreach($productResults['value'] as $key => $value){
            $product = $objectManager->create('Magento\Catalog\Model\Product');
            $productSku = $value['No'];
            $name = $value['Description'];
            $to = [" - "," / ","/"," & ",' ',"'"];
            $from = ['-',"-",'-','-','-',""];
            $urlKey = str_replace($to, $from, strtolower($value['Description'])).'-'.$value['No'];
            $storeIds = $value['Web_Store_Code'];
            $categoryCode1 = $value['Web_Category_Level_1_Code'];
            $categoryCode2 = $value['Web_Category_Level_2_Code'];
            $categoryCode3 = $value['Web_Category_Level_3_Code'];
            $startDate = $value['Start_Date'];
            $endDate = $value['End_Date'];
            if(!$product->getIdBySku($productSku)){
                $product->setSku($productSku);
                $product->setName($value['Description']); 
                // $product->setPrice(10); 
                $product->setAttributeSetId(4);
                $product->setWeight(1);
                $product->setVisibility(4);
                $product->setUrlKey($urlKey);
                $startDate = explode("T",$startDate)[0];
                $endDate = explode("T",$endDate)[0];
                $todayDate = $this->timezone->date()->format('Y-m-d');
                if($startDate <= $todayDate && $startDate != '0001-01-01' && ($endDate >= $todayDate || $endDate == '0001-01-01')){
                    $product->setStatus(1); 
                }else{
                    $product->setStatus(0); 
                }
                $stores = explode("|", $storeIds);
                $store_Id = [];
                $logger->info($productSku);
                $logger->info($storeIds);
                if($storeIds != ""){
                    foreach($stores as $store){
                        $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                        $result = $connection->fetchAll($getlastid);
                        $store_Id[] = $result[0]['store_id'];
                    }
                    $product->setWebsiteIds($store_Id); // store id
                }
                $product->save();
                $getCategoryId1 = "Select entity_id FROM " . $tableName2 . " where web_category_code = '$categoryCode1'";
                $result1 = $connection->fetchAll($getCategoryId1);
                $categoryId1 = $result1[0]['entity_id'];
                $getCategoryId2 = "Select entity_id FROM " . $tableName2 . " where web_category_code = '$categoryCode2'";
                $result2 = $connection->fetchAll($getCategoryId2);
                $categoryId2 = $result2[0]['entity_id'];
                $getCategoryId3 = "Select entity_id FROM " . $tableName2 . " where web_category_code = '$categoryCode3'";
                $result3 = $connection->fetchAll($getCategoryId3);
                $categoryId3 = $result3[0]['entity_id'];
                $categoryIds = [$categoryId1, $categoryId2, $categoryId3];
                $CategoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');
                $CategoryLinkRepository->assignProductToCategories($productSku, $categoryIds);
            }else{
                $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebSalesPrice?$format=application/json';
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
                $priceResults = json_decode($response_data,TRUE);
                // echo '<pre>';
                // print_r($priceResults);
                $getPriceDatas = [];
                foreach($priceResults['value'] as $key => $value){
                    $priceSku = $value['Item_No'];
                    $storeName = $value['Sales_Code'];
                    if($priceSku == $productSku){
                        $getPriceWithPriority = "Select price_group_code, priority FROM " . $tableName . " where price_group_code = '$storeName' LIMIT 1";
                        $result = $connection->fetchAll($getPriceWithPriority);
                        // $getPriorityData[] = $result[0];
                        $fields = [];
                        $fields['price_sku'] = $value['Item_No'];
                        $fields['Sales_Code'] = $value['Sales_Code'];
                        $fields['priceStartingDate'] = $value['Starting_Date'];
                        $fields['Unit_Price'] = $value['Unit_Price'];
                        $fields['getPriorityData'] = $result[0];
                        $getPriceDatas[] = $fields;
                    }
                }
                $data = [];
                foreach($getPriceDatas as $getPriceData){
                    $data[] = $getPriceData['getPriorityData']['priority'];
                }
                // echo '<pre>';
                // print_r($productSku);
                // $logger->info($productSku);
                // echo '<pre>';
                // print_r($data);
                // $logger->info($data);
                if($data){
                    $maxPriority = max($data);
                    $daysDifference = [];
                    foreach($getPriceDatas as $getPriceData){
                        $price_sku = $getPriceData['price_sku'];
                        $Sales_Code = $getPriceData['Sales_Code'];
                        $priceStartingDate = $getPriceData['priceStartingDate'];
                        $priority = $getPriceData['getPriorityData']['priority'];
                        if($priority == $maxPriority){
                            if($Sales_Code && $productSku == $price_sku){
                                $priceStartDate = explode("T",$priceStartingDate)[0];
                                $todayDate = $this->timezone->date()->format('Y-m-d');
                                $dateTimeObject1 = date_create($priceStartDate); 
                                $dateTimeObject2 = date_create($todayDate); 
                                $difference = date_diff($dateTimeObject1, $dateTimeObject2); 
                                $daysDifference[] = $difference->format('%a');
                            }
                        } 
                    }
                    $daysDiff = min($daysDifference);
                    foreach($getPriceDatas as $getPriceData){
                        $price_sku = $getPriceData['price_sku'];
                        $Sales_Code = $getPriceData['Sales_Code'];
                        $priceStartingDate = $getPriceData['priceStartingDate'];
                        $priority = $getPriceData['getPriorityData']['priority'];
                        if($priority == $maxPriority){
                            if($Sales_Code && $productSku == $price_sku){
                                $priceStartDate = explode("T",$priceStartingDate)[0];
                                $todayDate = $this->timezone->date()->format('Y-m-d');
                                $dateTimeObject1 = date_create($priceStartDate); 
                                $dateTimeObject2 = date_create($todayDate); 
                                $difference = date_diff($dateTimeObject1, $dateTimeObject2); 
                                $daysDifference = $difference->format('%a');
                                if($daysDifference == $daysDiff){
                                    $unitPrice = $getPriceData['Unit_Price'];
                                    $getproduct = "Select entity_id FROM " . $tableName3 . " where sku = $productSku";
                                    $result = $connection->fetchAll($getproduct);
                                    $getproductid = $result[0]['entity_id'];
                                    $getStoreId = "Select magento_store_id FROM " . $tableName . " where price_group_code = '$Sales_Code'";
                                    $result = $connection->fetchAll($getStoreId);
                                    $getStore = $result[0]['magento_store_id'];
                                    $product->load($getproductid);
                                    try{
                                        // echo "<pre>";
                                        // print_r($Sales_Code);
                                        // echo "<pre>";
                                        // print_r($unitPrice);
                                        // echo "<pre>";
                                        // print_r($productSku);
                                        // echo "<pre>";
                                        // print_r($getStore);
                                        $product->setWebsiteIds(array($getStore));
                                        // $product->setSku($productSku);
                                        // $product->setName($name);
                                        // $product->setWeight(1);
                                        // $product->setVisibility(4);
                                        // $product->setPrice($unitPrice);
                                        $product->save();
                                        $this->action->updateAttributes([$getproductid],['name' => $name, 'url_key' => $urlKey, 'price' => $unitPrice],$getStore);
                                    }catch(\Exception $e){
                                        echo $e->getMessage();
                                    }
                                }
                            }
                        }
                    }
                }else{
                    echo "<pre>";
                    print_r($productSku." product not found");
                }
            }
        }
	}
}
