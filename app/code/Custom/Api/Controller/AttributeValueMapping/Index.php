<?php

namespace Custom\Api\Controller\AttributeValueMapping;

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
        // EavSetupFactory $eavSetupFactory,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        // $this->eavSetupFactory = $eavSetupFactory;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->recursivelyAPICall();
        // reindexAll
        $reindexAll = $this->reindexAll();
        // // flushCache
        // $flushCache = $this->flushCache();
    }

    public function recursivelyAPICall($offset = 0)
    {
        $post = $this->getRequest()->getPostValue();
        $response = array();
        $top = 10;
        $skip=$offset;
        // WebCategoryLink
        $item_sku = @$_GET['item_sku'];
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        if (isset($item_sku) && !empty($item_sku)) {
            $category_link_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json&$filter=No%20eq%20%27'.$item_sku.'%27&$top='.$top.'&$skip='.$skip;
        }else{
            $category_link_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json&$top='.$top.'&$skip='.$skip;
        }        
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $category_link_url,
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
        $category_data = json_decode($response_data,TRUE);
        echo '<pre>';
        // print_r($category_data);

        if (!empty($category_data['value'])) {
            foreach ($category_data['value'] as $category_item) {
                $product_sku = $category_item['No'];
                echo '<br>';
                echo 'SKU : '.$product_sku;              
                echo '<br>';
                if (!empty($product_sku)) {
                    $this->WebAttributeValueMapping($product_sku);
                }             
            }
            // call recursive func
            if (count($category_data['value']) >= $top) {
                $newOffset = $offset + $top;
                $this->recursivelyAPICall($newOffset);
            }
        }
    }
    
    public function WebAttributeValueMapping($product_sku)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebAttributeValueMapping?$format=application/json&$filter=Item_No%20eq%20%27' . $product_sku . '%27';
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
        $response_array = json_decode($response_data,TRUE);
        echo '<pre>';
        print_r($response_array);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('catalog_product_entity');
        $tableName3 = $resource->getTableName('catalog_product_entity_int');
        $tableName4 = $resource->getTableName('eav_attribute');
        $tableName5 = $resource->getTableName('eav_attribute_option');
        $tableName6 = $resource->getTableName('catalog_product_entity_text');
        $tableName7 = $resource->getTableName('eav_attribute_option_value');
        foreach ($response_array['value'] as $key => $value) {
            $product = $objectManager->create('Magento\Catalog\Model\Product');
            $productSku = $value['Item_No'];
            $attributeId = $value['Web_Attribute_ID'];
            $attributeName = $value['Web_Attribute_Name'];
            $attributeValue = $value['Web_Attribute_Value'];
            $attributeType = $value['Attribute_Type'];
            $from = [' ', '-', '&', '$'];
            $to = ['_', '_', '_', '_'];
            $attributeSlug = str_replace($from, $to, strtolower($attributeName));
            if ($product->getIdBySku($productSku)) {
                $getProductId = "Select entity_id, row_id FROM " . $tableName1 . " where sku = '$productSku'";
                $result1 = $connection->fetchAll($getProductId);
                $productId = $result1[0]['entity_id'];
                $productRawId = $result1[0]['row_id'];
                $getAttributeId = "Select attribute_id, attribute_code, backend_type FROM " . $tableName4 . " where attribute_code = '$attributeSlug'";
                $result2 = $connection->fetchAll($getAttributeId);
                if($result2){
                    $attributeId = $result2[0]['attribute_id'];
                    $backendType = $result2[0]['backend_type'];
                    if($backendType == 'text'){
                        $getOptionId = "Select option_id FROM " . $tableName5 . " where attribute_id = '$attributeId'";
                        $getOptionIds = $connection->fetchAll($getOptionId);
                        if($attributeType == 'Option'){
                            foreach($getOptionIds as $optionId){
                                $optionId = $optionId['option_id'];
                                $getValues = "Select value FROM " . $tableName7 . " where option_id = $optionId";
                                $resultGetValues = $connection->fetchAll($getValues);
                                $getValue= $resultGetValues[0]['value'];
                                if($getValue == $attributeValue){
                                    $query = $connection->select()->from('catalog_product_entity_text', ['*'])->where('attribute_id = ?', $attributeId)->where('row_id = ?', $productRawId);
                                    $result = $connection->fetchRow($query);
                                    if (empty($result)) {
                                        $sql = "insert into " . $tableName6 . " (attribute_id, store_id, value, row_id) Values ($attributeId, 0, $optionId, $productRawId)";
                                        $connection->query($sql);
                                    }else{
                                        $sql = 'Update ' . $tableName6 . ' Set value = "'.$optionId.'" where attribute_id = "'.$attributeId.'" && row_id = "'.$productRawId.'" ';
                                        $connection->query($sql);
                                    }
                                }
                            } 
                        }
                        if(($attributeType == 'Text') || ($attributeType == 'Decimal')){
                            $query = $connection->select()->from('catalog_product_entity_text', ['*'])->where('attribute_id = ?', $attributeId)->where('row_id = ?', $productRawId);
                            $result = $connection->fetchRow($query);
                            if (empty($result)) {
                                $sql = 'insert into ' . $tableName6 . ' (attribute_id, store_id, value, row_id) Values ('.$attributeId.', 0, "'.$attributeValue.'", '.$productRawId.')';
                                $connection->query($sql);
                            }else{
                                $sql = 'Update ' . $tableName6 . ' Set value = "'.$attributeValue.'" where attribute_id = "'.$attributeId.'" && row_id = "'.$productRawId.'" ';
                                $connection->query($sql);
                            }
                        }
                    }elseif($backendType == 'int'){
                        if($attributeType == 'Boolean'){
                            $getProductId = "Select entity_id, row_id FROM " . $tableName1 . " where sku = '$productSku'";
                            $result1 = $connection->fetchAll($getProductId);
                            $productRawId = $result1[0]['row_id'];
                            $query = $connection->select()->from('catalog_product_entity_int', ['*'])->where('attribute_id = ?', $attributeId)->where('row_id = ?', $productRawId);
                            $result = $connection->fetchRow($query);
                            if (empty($result)) {
                                $sql = 'insert into ' . $tableName3 . ' (attribute_id, store_id, value, row_id) Values ('.$attributeId.', 0, "'.$attributeValue.'", '.$productRawId.')';
                                $connection->query($sql);
                            }else{
                                $sql = 'Update ' . $tableName3 . ' Set value = "'.$attributeValue.'" where attribute_id = "'.$attributeId.'" && row_id = "'.$productRawId.'" ';
                                $connection->query($sql);
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
