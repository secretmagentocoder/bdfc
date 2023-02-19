<?php

namespace Custom\Api\Controller\ItemBrandLinkList;

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
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $brand_name_list = [];
        $this->recursivelyAPICall($offset = 0, $brand_name_list);
        // reindexAll
        $this->reindexAll();
        // // flushCache
        // $this->flushCache();
    }

    public function recursivelyAPICall($offset, $brand_name_list)
    {
        $response = array();
        $top = 1000;
        $skip = $offset;
        $item_sku = @$_GET['item_sku'];
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        if (isset($item_sku) && !empty($item_sku)) {
            $attribute_url = $host.'/Company(%27'.$company.'%27)/WebItemList?$format=application/json&$filter=No%20eq%20%27' . $item_sku . '%27&$skip=' . $skip . '&$top=' . $top;
        } else {
            $attribute_url = $host.'/Company(%27'.$company.'%27)/WebItemList?$format=application/json&$skip=' . $skip . '&$top=' . $top;
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
        $brandsResults = json_decode($response_data, TRUE);
        echo '<pre>';
        print_r($brandsResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('magetop_brand');
        $tableName2 = $resource->getTableName('magetop_brand_product');
        $tableName3 = $resource->getTableName('catalog_product_entity');

        // $brand_name_list = [];
        foreach ($brandsResults['value'] as $value) {
            $product = $objectManager->create('Magento\Catalog\Model\Product');
            $productSku = $value['No']; //SKU
            $brand_name = $value['Brand_Description']; //Brand Name
            
            echo '<br>';
            echo 'SKU : '.$productSku;              
            echo '<br>';
            $productID= $product->getIdBySku($productSku);
            // die;

            if ($product->getIdBySku($productSku)) 
            {
                $getBrandId = "Select brand_id FROM " . $tableName1 . " where name = '" . $value['Brand_Description'] . "'";
                $result = $connection->fetchAll($getBrandId);
                var_dump($result);
                if ($result) {
                    $brand_id = $getoptionid = $result[0]['brand_id'];
                    $_product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($productSku);
                    $_product->setCustomAttribute('product_brand', $brand_id);
                    $_product->save();
                    $getproduct = "Select entity_id FROM " . $tableName3 . " where sku = $productSku";
                    $result1 = $connection->fetchAll($getproduct);
                    $getproductid = $result1[0]['entity_id'];
                    $getBrands = "Select brand_id FROM " . $tableName2 . " where product_id = $getproductid";
                    $brandResults = $connection->fetchAll($getBrands);
                    foreach ($brandResults as $getBrandId) {
                        $getBrandId = $getBrandId['brand_id'];
                        $sql = "Delete FROM " . $tableName2 . " Where product_id = $getproductid && brand_id = $getBrandId";
                        $connection->query($sql);
                    }
                    $query = $connection->select()->from('magetop_brand_product', ['*'])->where('brand_id = ?', $brand_id)->where('product_id = ?', $getproductid);
                    $result = $connection->fetchRow($query);
                    if (empty($result)) {
                        $sql = "insert into " . $tableName2 . " (brand_id, product_id, position) Values ($brand_id, $getproductid, 0)";
                        $connection->query($sql);
                    }
                    $fields = [];
                    $fields['brand_id'] = $brand_id;
                    $fields['product_id'] = $getproductid;
                    $brand_name_list[] = $fields;
                }else{
                    echo "Savibg";
                    $sql = "Delete FROM " . $tableName2 . " Where product_id = $productID";
                    $connection->query($sql);
                    $_product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($productSku);
                    $_product->setCustomAttribute('product_brand', '');
                    $_product->save();
                }
            }
        }
        if (count($brandsResults['value']) >= $top) {
            $newOffset = $offset + $top;
            $this->recursivelyAPICall($newOffset, $brand_name_list);
        }
    }

    public function brandsDisableCall($brand_name_list)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('magetop_brand');
        $tableName2 = $resource->getTableName('magetop_brand_product');
        $tableName3 = $resource->getTableName('catalog_product_entity');

        $selectLists = "Select brand_id, product_id FROM " . $tableName2;
        // $selectLists = "Select brand_id, product_id FROM " . $tableName2. " LIMIT ".$top. " offset ".$skip;
        $getValueLists = $connection->fetchAll($selectLists);
        if (count($getValueLists) > count($brand_name_list)) {
            $resultDifferences = $this->check_diff_multi($getValueLists, $brand_name_list);
            // print_r($resultDifferences);
            foreach ($resultDifferences as $resultDifference) {
                $product_id = $resultDifference['product_id'];
                $brand_id = $resultDifference['brand_id'];
                $sql = "Delete FROM " . $tableName2 . " Where product_id = $product_id && brand_id = $brand_id";
                $connection->query($sql);
            }
        }
    }


    public function check_diff_multi($arraya, $arrayb)
    {
        foreach ($arraya as $keya => $valuea) {
            if (in_array($valuea, $arrayb)) {
                unset($arraya[$keya]);
            }
        }
        return $arraya;
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
