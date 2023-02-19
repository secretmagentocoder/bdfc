<?php

namespace Custom\Api\Controller\ConfigurableProduct;

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
        $response = array();
        // $top = 100;
        // $skip = $offset;
        $item_sku = @$_GET['item_sku'];
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        if (isset($item_sku) && !empty($item_sku)) {
            $attribute_url = $host.'/Company(%27'.$company.'%27)/WebItemList?$format=application/json&$filter=Master_Product%20eq%20%27' . $item_sku . '%27&$skip=' . $skip . '&$top=' . $top;
        } else {
            // $attribute_url = 'http://88.201.70.74:7048/DynamicsNAV71/OData/Company(%27BDFC%27)/WebItemList?$format=application/json&$skip=' . $skip . '&$top=' . $top;
            $attribute_url = $host.'/Company(%27'.$company.'%27)/WebItemList?$format=application/json';
        }
        // $masterProduct = "509431";
        // $attribute_url = 'http://88.201.70.74:7048/DynamicsNAV71/OData/Company(%27BDFC%27)/WebItemList?$format=application/json&$filter=Master_Product%20eq%20%27' . $masterProduct . '%27';
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
        // echo '<pre>';
        // print_r($productResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName = $resource->getTableName('web_store_price_group');
        $tableName1 = $resource->getTableName('web_store_num');
        $tableName3 = $resource->getTableName('catalog_product_entity');
        $getProductLists = [];
        $childSku = [];
        foreach ($productResults['value'] as $key => $value) {
            $product = $objectManager->create('Magento\Catalog\Model\Product');
            $productSku = $value['No'];
            $name = $value['Description'];
            $barcodeNo = $value['BarcodeNo'];
            $hsCode = $value['HS_code'];
            $countryCode = $value['Country_Region_of_Origin_Code'];
            $masterProduct = $value['Master_Product'];
            $to = [" - ", " / ", "/", " & ", ' ', "'"];
            $from = ['-', "-", '-', '-', '-', ""];
            $urlKey = str_replace($to, $from, strtolower($name)) . '-' . $productSku;
            $masterProductSku = [];
            if($masterProduct == $productSku){
                if (!$product->getIdBySku($masterProduct)) {
                    $uniqueMasterIds = collect($productResults['value'])->groupby('Master_Product');
                    $childSku['parent_sku'] = $masterProduct;
                    $childSku['child_id'] = collect($uniqueMasterIds[$masterProduct])->pluck('No');
                    $this->createVariantProduct($masterProduct, $productSku, $childSku, $name, $urlKey, $barcodeNo, $hsCode, $countryCode);
                }else{
                    $uniqueMasterIds = collect($productResults['value'])->groupby('Master_Product');
                    $childSku['parent_sku'] = $masterProduct;
                    $childSku['child_id'] = collect($uniqueMasterIds[$masterProduct])->pluck('No');
                    echo "<pre>";
                    print_r($childSku['parent_sku']);
                    echo "<pre>";
                    print_r($childSku['child_id']);
                    $this->updateVariantProduct($masterProduct, $productSku, $childSku, $name, $urlKey, $barcodeNo, $hsCode, $countryCode);
                }
            }
        }
        // // print_r($masterProductSku);
        // $getSkuLists = [];
        // // $selectSku = "Select sku FROM " . $tableName3;
        // $selectSku = "Select sku FROM " . $tableName3 . " LIMIT " . $top . " offset " . $skip;
        // $getValueLists = $connection->fetchAll($selectSku);
        // foreach ($getValueLists as $getValueList) {
        //     // echo "<pre>";
        //     // echo "getting sku";
        //     $getSkuLists[] = $getValueList['sku'];
        // }
        // $results = array_diff($getSkuLists, $getProductLists);
        // $getToken = $this->generateAccessToken();
        // foreach ($results as $skudata) {
        //     // echo "<pre>";
        //     // echo "status updated";
        //     $this->updateStatus($getToken, $skudata);
        // }
        // if (count($productResults['value']) >= $top) {
        //     $newOffset = $offset + $top;
        //     $this->recursivelyAPICall($newOffset);
        // }
    }

    public function createVariantProduct($masterProduct, $productSku, $childSku, $name, $urlKey, $barcodeNo, $hsCode, $countryCode)
    {
        $response = array();
        $curl = curl_init();
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json&$filter=No%20eq%20%27' . $masterProduct . '%27';
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
        $variantProductResults = json_decode($response_data, TRUE);
        echo "<pre>";
        // print_r($variantProductResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName1 = $resource->getTableName('web_store_num');
        $tableName3 = $resource->getTableName('catalog_product_entity');
        if($variantProductResults['value']){
            $storeIds = $variantProductResults['value'][0]['Web_Store_Code'];
            $startDate = $variantProductResults['value'][0]['Start_Date'];
            $endDate = $variantProductResults['value'][0]['End_Date'];
            $getStores = explode("|", $storeIds);
            $store_Id = [];
            foreach ($getStores as $getStore) {
                $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$getStore'";
                $result = $connection->fetchAll($getlastid);
                if ($result) {
                    $store_Id[] = $result[0]['store_id'];
                }
            }
            if($store_Id){
                try {
                    $configurable_product = $objectManager->create('Magento\Catalog\Model\Product');
                    $configurable_product->setSku($masterProduct);
                    $configurable_product->setName($name);
                    $configurable_product->setPrice(0);
                    $configurable_product->setAttributeSetId(4);
                    $configurable_product->setWeight(1);
                    $configurable_product->setVisibility(4);
                    $configurable_product->setStockData(array(
                        'qty' => 10,
                        'is_in_stock' => 1
                    ));
                    $configurable_product->setUrlKey($urlKey);
                    $startDate = explode("T", $startDate)[0];
                    $endDate = explode("T", $endDate)[0];
                    $todayDate = $this->timezone->date()->format('Y-m-d');
                    if (($startDate == '0001-01-01' && $endDate >= $todayDate) || ($startDate <= $todayDate && $endDate == '0001-01-01') || ($startDate <= $todayDate && $endDate >= $todayDate)) {
                        $configurable_product->setStatus(1);
                    } else {
                        $configurable_product->setStatus(0);
                    }
                    $configurable_product->setWebsiteIds($store_Id);
                    $configurable_product->setBarcodeNo($barcodeNo);
                    $configurable_product->setHsCode($hsCode);
                    $configurable_product->setTypeId("simple");
                    $configurable_product->setCountryOfManufacture($countryCode);
                    $configurable_product->save();
                    // echo "<pre>";
                    // print_r($childSku['variant_product']);
                    $getParentId = $configurable_product->getId();
                    $parentSku = $childSku['parent_sku'];
                    if($childSku['child_id']){
                        $getChildId = [];
                        $getChildArr = [];
                        foreach($childSku['child_id'] as $child){
                            $getproduct = "Select entity_id FROM " . $tableName3 . " where sku = $child";
                            $result2 = $connection->fetchAll($getproduct);
                            if($result2){
                                $getChildId[] = $result2[0]['entity_id'];
                                $getChildArr[] = $child;
                            }
                        }
                        if (($key = array_search($getParentId, $getChildId)) !== false) {
                            unset($getChildId[$key]);
                        }
                        if (($key = array_search($parentSku, $getChildArr)) !== false) {
                            unset($getChildArr[$key]);
                        }
                        try{
                            $this->WebAttributeValueMapping($configurable_product, $getChildId, $getParentId, $parentSku, $getChildArr);
                        } catch (Exception $e) {
                            echo "<pre>";
                            print_r($e->getMessage());
                            exit;
                        }
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }

    public function updateVariantProduct($masterProduct, $productSku, $childSku, $name, $urlKey, $barcodeNo, $hsCode, $countryCode)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $response = array();
        $curl = curl_init();
        $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json&$filter=No%20eq%20%27' . $masterProduct . '%27';
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
        $variantProductResults = json_decode($response_data, TRUE);
        echo "<pre>";
        // print_r($variantProductResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName1 = $resource->getTableName('web_store_num');
        $tableName3 = $resource->getTableName('catalog_product_entity');
        if($variantProductResults['value']){
            $storeIds = $variantProductResults['value'][0]['Web_Store_Code'];
            $startDate = $variantProductResults['value'][0]['Start_Date'];
            $endDate = $variantProductResults['value'][0]['End_Date'];
            $getproduct = "Select entity_id FROM " . $tableName3 . " where sku = $masterProduct";
            $result = $connection->fetchAll($getproduct);
            $getproductid = $result[0]['entity_id'];
            $configurable_product = $objectManager->create('Magento\Catalog\Model\Product');
            $configurable_product->load($getproductid);
            $startDate = explode("T", $startDate)[0];
            $endDate = explode("T", $endDate)[0];
            $todayDate = $this->timezone->date()->format('Y-m-d');
            $getStores = explode("|", $storeIds);
            $store_Id = [];
            try {
                foreach ($getStores as $getStore) {
                    $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$getStore'";
                    $result = $connection->fetchAll($getlastid);
                    if($result){
                        $stores = $result[0]['store_id'];
                        if ($startDate == '0001-01-01' && $endDate == '0001-01-01') {
                            $this->action->updateAttributes([$getproductid], ['status' => 0], $stores);
                            $this->action->updateAttributes([$getproductid], ['status' => 0], 0);
                        } elseif (($startDate == '0001-01-01' && $endDate >= $todayDate) || ($startDate <= $todayDate && $endDate == '0001-01-01') || ($startDate <= $todayDate && $endDate >= $todayDate)) {
                            $this->action->updateAttributes([$getproductid], ['status' => 1], $stores);
                            $this->action->updateAttributes([$getproductid], ['status' => 1], 0);
                        } else {
                            $this->action->updateAttributes([$getproductid], ['status' => 0], $stores);
                            $this->action->updateAttributes([$getproductid], ['status' => 0], 0);
                        }
                        $store_Id[] = $result[0]['store_id'];
                    }
                }
                // echo "<pre>";
                // print_r($store_Id);
                if($store_Id){
                    // print_r($store_Id);
                    $configurable_product->setName($name);
                    $configurable_product->setWebsiteIds($store_Id);
                    $configurable_product->setBarcodeNo($barcodeNo);
                    $configurable_product->setTypeId("simple");
                    $configurable_product->setHsCode($hsCode);
                    $configurable_product->setCountryOfManufacture($countryCode);
                    $configurable_product->setStockData(array(
                        'qty' => 10,
                        'is_in_stock' => 1
                    ));
                    $configurable_product->save();
                    $getParentId = $getproductid;
                    $parentSku = $childSku['parent_sku'];
                    if($childSku['child_id']){
                        echo "<pre>";
                        print_r($childSku['child_id']);
                        $getChildId = [];
                        $getChildArr = [];
                        foreach($childSku['child_id'] as $child){
                            $getproduct = "Select entity_id FROM " . $tableName3 . " where sku = $child";
                            $result2 = $connection->fetchAll($getproduct);
                            if($result2){
                                $getChildId[] = $result2[0]['entity_id'];
                                $getChildArr[] = $child;
                            }
                        }
                        echo "<pre>";
                        print_r($getChildArr);
                        echo "<pre>";
                        print_r($getChildId);
                        if (($key = array_search($getproductid, $getChildId)) !== false) {
                            unset($getChildId[$key]);
                        }
                        if (($key = array_search($parentSku, $getChildArr)) !== false) {
                            unset($getChildArr[$key]);
                        }
                        // assign simple product ids
                        echo "<pre>";
                        print_r($getChildArr);
                        echo "<pre>";
                        print_r($getChildId);
                        try{
                            $this->WebAttributeValueMapping($configurable_product, $getChildId, $getParentId, $parentSku, $getChildArr);
                        } catch (Exception $e) {
                            echo "<pre>";
                            print_r($e->getMessage());
                            exit;
                        }
                    }
                    echo "<pre>";
                    echo "product updated";
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    public function WebAttributeValueMapping($configurable_product, $getChildId, $getParentId, $parentSku, $getChildArr)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebAttributeValueMapping?$format=application/json&$filter=Item_No%20eq%20%27' . $parentSku . '%27';
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $attribute_url,
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $user.':'.$password,
            CURLOPT_HTTPHEADER => $header,
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
        $productAttrResults = json_decode($response_data,TRUE);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName1 = $resource->getTableName('web_store_num');
        $tableName3 = $resource->getTableName('catalog_product_entity');
        echo '<pre>';
        print_r($productAttrResults);
        foreach ($productAttrResults['value'] as $key => $value) {
            $webAttributeName = $value['Web_Attribute_Name'];
            $attributeIsVariant = $value['Attribute_is_Variant'];
            $itemNo = $value['Item_No'];
            if($itemNo == $parentSku){
                echo "<pre>";
                print_r($parentSku);
                echo "<pre>";
                print_r($getChildId);
                if($attributeIsVariant == 1){
                    // if($attributeIsVariant == 1){
                        if($getChildId){
                        echo "<pre>";
                        print_r($webAttributeName);
                        $getChildListId = [];
                        foreach($getChildId as $setChildId){
                            $getproduct = "Select type_id FROM " . $tableName3 . " where entity_id = $setChildId";
                            $result2 = $connection->fetchAll($getproduct);
                            if($result2){
                                if($result2[0]['type_id'] == 'simple'){
                                    $getChildListId[] = $setChildId;
                                }
                            }
                        }
                        echo "<pre>";
                        print_r($getChildListId);
                        // $getChildListId = array(2713, 2715);
                        // $associatedProductIds = [2713, 2715]; // Add Your Associated Product Ids.
                        if($getChildListId){
                            $variant_attr_id = $configurable_product->getResource()->getAttribute($webAttributeName)->getId();
                            $configurable_product = $objectManager->create('Magento\Catalog\Model\Product')->load($getParentId); // Load Configurable Product
                            $configurable_product->setTypeId("configurable"); // Setting Product Type As Configurable
                            $configurable_product->getTypeInstance()->setUsedProductAttributeIds(array($variant_attr_id), $configurable_product);
                            $configurableAttributesData = $configurable_product->getTypeInstance()->getConfigurableAttributesAsArray($configurable_product);
                            $configurable_product->setCanSaveConfigurableAttributes(true);
                            $configurable_product->setConfigurableAttributesData($configurableAttributesData);
                            $configurableProductsData = array();
                            $configurable_product->setConfigurableProductsData($configurableProductsData);
                            $configurable_product->save();
                            // assign simple product ids
                            // $configurable_product = $objectManager->create('Magento\Catalog\Model\Product')->load($getParentId); // Load Configurable Product
                            echo "<pre>";
                            print_r($getChildListId);
                            $configurable_product->setAssociatedProductIds($getChildListId); // Setting Associated Products
                            $configurable_product->setCanSaveConfigurableAttributes(true);
                            $configurable_product->save();
                            echo "<pre>";
                            echo "variant product updated";
                        }
                    }
                }
            }
        }
    }

    public function generateAccessToken()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $token_url = $baseUrl . 'rest/all/V1/integration/admin/token';
        $username = $this->navConfigProvider->getMagentoUserName();
        $password = $this->navConfigProvider->getMagentoPassword();
        //Authentication rest API magento2, For get access token
        $ch = curl_init();
        $data = array("username" => $username, "password" => $password);
        $data_string = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $token = curl_exec($ch);
        $accessToken = json_decode($token);
        return $accessToken;
    }

    public function updateStatus($getToken, $skudata)
    {
        $skudata = urlencode($skudata);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $enableDisableUrl = $baseUrl . 'rest/all/V1/products/' . $skudata;
        $curl = curl_init($enableDisableUrl);
        $data_string = '{
            "product": {
              "status": 1
            }
        }';
        $headers = array(
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Bearer {$getToken}",
        );
        curl_setopt($curl, CURLOPT_URL, $enableDisableUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $resp = curl_exec($curl);
        // $server_output = curl_exec($ch);
        $info = curl_getinfo($curl);
        if ($info['http_code'] == 200) {
            echo "<pre>";
            // print_r("status updated");
        }
        curl_close($curl);
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
