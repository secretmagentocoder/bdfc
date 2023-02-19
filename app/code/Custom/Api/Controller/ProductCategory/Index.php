<?php

namespace Custom\Api\Controller\ProductCategory;

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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->action = $action;
        $this->timezone = $timezone;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->recursivelyAPICall();
        // reindexAll
        $this->reindexAll();

        // flushCache
        // $this->flushCache();
    }

    public function recursivelyAPICall($offset = 0)
    {
        $sku=isset($_GET['sku'])?$_GET['sku']:'';
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $response = array();
        $top = 10;
        $skip = $offset;
        $item_sku = @$_GET['item_sku'];
        if (isset($item_sku) && !empty($item_sku)) {
            $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json&$filter=No%20eq%20%27' . $item_sku . '%27&$top=' . $top . '&$skip=' . $skip;
        } else {
            $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebCategoryLink?$format=application/json&$top=' . $top . '&$skip=' . $skip;
        }
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName1 = $resource->getTableName('catalog_product_entity'); // will get product-id
        $tableName2 = $resource->getTableName('catalog_category_entity'); // will get category-code
        $tableName3 = $resource->getTableName('catalog_category_product');
        echo "<pre>";
        print_r($productResults);
        foreach ($productResults['value'] as $key => $value) {
            $product = $objectManager->create('Magento\Catalog\Model\Product');
            $productSku = $value['No'];
            $storeIds = $value['Web_Store_Code'];
            $startDate = $value['Start_Date'];
            $endDate = $value['End_Date'];
            $categoryCode1 = $value['Web_Category_Level_1_Code'];
            $categoryCode2 = $value['Web_Category_Level_2_Code'];
            $categoryCode3 = $value['Web_Category_Level_3_Code'];
            if ($product->getIdBySku($productSku)) {
                $getCategoryId1 = "Select entity_id FROM " . $tableName2 . " where web_category_code = '$categoryCode1'";
                $result1 = $connection->fetchAll($getCategoryId1);
                $categoryId1 = $result1[0]['entity_id'];
                if ($categoryCode3) {
                    $getCategoryId2 = "Select entity_id FROM " . $tableName2 . " where web_category_code = '$categoryCode2'";
                    $result2 = $connection->fetchAll($getCategoryId2);
                    $categoryId2 = $result2[0]['entity_id'];
                    $getCategoryId3 = "Select entity_id FROM " . $tableName2 . " where web_category_code = '$categoryCode3'";
                    $result3 = $connection->fetchAll($getCategoryId3);
                    $categoryId3 = $result3[0]['entity_id'];
                    $categoryIds = [$categoryId1, $categoryId2, $categoryId3];
                    $endDate = explode("T", $endDate)[0];
                    $startDate = explode("T", $startDate)[0];
                    $todayDate = $this->timezone->date()->format('Y-m-d');
                    $getproduct = "Select entity_id FROM " . $tableName1 . " where sku = $productSku";
                    $result = $connection->fetchAll($getproduct);
                    $getproductid = $result[0]['entity_id'];
                    $product->load($getproductid);
                    $getCategoryLists = $product->getCategoryIds();
                    foreach ($getCategoryLists as $category_id) {
                        $sql = "Delete FROM " . $tableName3 . " Where product_id = $getproductid && category_id = $category_id";
                        $connection->query($sql);
                    }
                    $CategoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');
                    if (($startDate == '0001-01-01' && $endDate >= $todayDate) || ($startDate <= $todayDate && $endDate == '0001-01-01') || ($startDate <= $todayDate && $endDate >= $todayDate)) {
                        $CategoryLinkRepository->assignProductToCategories($productSku, $categoryIds);
                    } else {
                        echo $productSku . " Cannot link product with category " . "<br/>";
                    }
                } elseif ($categoryCode2) {
                    $getCategoryId2 = "Select entity_id FROM " . $tableName2 . " where web_category_code = '$categoryCode2'";
                    $result2 = $connection->fetchAll($getCategoryId2);
                    $categoryId2 = $result2[0]['entity_id'];
                    $categoryIds = [$categoryId1, $categoryId2];
                    $endDate = explode("T", $endDate)[0];
                    $startDate = explode("T", $startDate)[0];
                    $todayDate = $this->timezone->date()->format('Y-m-d');
                    $getproduct = "Select entity_id FROM " . $tableName1 . " where sku = $productSku";
                    $result = $connection->fetchAll($getproduct);
                    $getproductid = $result[0]['entity_id'];
                    $product->load($getproductid);
                    $getCategoryLists = $product->getCategoryIds();
                    foreach ($getCategoryLists as $category_id) {
                        $sql = "Delete FROM " . $tableName3 . " Where product_id = $getproductid && category_id = $category_id";
                        $connection->query($sql);
                    }
                    $CategoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');
                    if (($startDate == '0001-01-01' && $endDate >= $todayDate) || ($startDate <= $todayDate && $endDate == '0001-01-01') || ($startDate <= $todayDate && $endDate >= $todayDate)) {
                        $CategoryLinkRepository->assignProductToCategories($productSku, $categoryIds);
                    } else {
                        echo $productSku . " Cannot link product with category " . "<br/>";
                    }
                } else {
                    $categoryIds = [$categoryId1];
                    $endDate = explode("T", $endDate)[0];
                    $startDate = explode("T", $startDate)[0];
                    $todayDate = $this->timezone->date()->format('Y-m-d');
                    $getproduct = "Select entity_id FROM " . $tableName1 . " where sku = $productSku";
                    $result = $connection->fetchAll($getproduct);
                    $getproductid = $result[0]['entity_id'];
                    $product->load($getproductid);
                    $getCategoryLists = $product->getCategoryIds();
                    foreach ($getCategoryLists as $category_id) {
                        $sql = "Delete FROM " . $tableName3 . " Where product_id = $getproductid && category_id = $category_id";
                        $connection->query($sql);
                    }
                    $CategoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');
                    if (($startDate == '0001-01-01' && $endDate >= $todayDate) || ($startDate <= $todayDate && $endDate == '0001-01-01') || ($startDate <= $todayDate && $endDate >= $todayDate)) {
                        $CategoryLinkRepository->assignProductToCategories($productSku, $categoryIds);
                    } else {
                        echo $productSku . " Cannot link product with category " . "<br/>";
                    }
                }
            } else {
                echo $productSku . " Product Not Found " . "<br/>";
            }
        }
        if (count($productResults['value']) >= $top) {
            $newOffset = $offset + $top;
            $this->recursivelyAPICall($newOffset);
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
