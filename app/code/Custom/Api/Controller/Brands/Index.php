<?php

namespace Custom\Api\Controller\Brands;

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
        // $this->reindexAll();

        // flushCache
        // $this->flushCache();
    }

    public function recursivelyAPICall($offset, $brand_name_list)
    {
        $response = array();
        $top = 500;
        $skip = $offset;
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company(%27'.$company.'%27)/WebBrands?$format=application/json&$skip=' . $skip . '&$top=' . $top;
      
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $attribute_url,
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $user.':'.$password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response_data = curl_exec($curl);
        curl_close($curl);
        $brandsResults = json_decode($response_data, TRUE);
        echo "<pre>";
        print_r($brandsResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $options = [];
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('magetop_brand');
        $tableName2 = $resource->getTableName('magetop_brand_store');
        $tableName3 = $resource->getTableName('web_store_num');
        // $brand_name_list = [];
        foreach ($brandsResults['value'] as $value) {
            $brand_id = $value['Brand_ID'];
            $brand_name = $value['Brand_Name'];
            $to = [' ', "'"];
            $from = ['-', ""];
            $brand_url_key = str_replace($to, $from, strtolower($value['Brand_Name']));
            $brand_description = $value['Description'];
            $brand_store = $value['Store'];
            $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
                ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
            $query = $connection->select()->from('magetop_brand', ['name'])->where('brand_id = ?', $brand_id);
            $result = $connection->fetchRow($query);
            if (!$result) {
                if ($brand_store) {
                    $insertBrand = 'insert into ' . $tableName1 . ' (brand_id, name, url_key, description, page_layout, group_id) Values (' . $brand_id . ', "' . $brand_name . '", "' . $brand_url_key . '", "' . $brand_description . '", "2columns-left", 2)';
                    $connection->query($insertBrand);
                    // we Get Store ID 
                    // $store_ids = array('2', '3', '4');
                    $getStoreIds = explode("|", $brand_store);
                    foreach ($getStoreIds as $store) {
                        $getlastid = "Select store_id FROM " . $tableName3 . " where store_num = '$store'";
                        $result = $connection->fetchAll($getlastid);
                        $store_Id = $result[0]['store_id'];
                        // if(in_array($store_Id, $store_ids)){
                        $sql = 'insert into ' . $tableName2 . ' (brand_id, store_id) Values (' . $brand_id . ', "' . $store_Id . '")';
                        $connection->query($sql);
                        // }
                    }
                }
            } else {
                $sql = 'Update ' . $tableName1 . ' Set name = "' . $brand_name . '",description = "' . $brand_description . '",status = 1 where brand_id = "' . $brand_id . '" ';
                $connection->query($sql);
                // we Get Store ID
                $getStoreIds = explode("|", $brand_store);
                $store_Id = [];
                foreach ($getStoreIds as $store) {
                    $getlastid = "Select store_id FROM " . $tableName3 . " where store_num = '$store'";
                    $result = $connection->fetchAll($getlastid);
                    $store_Id[] = $result[0]['store_id'];
                }
                $getStoreId = 'Select store_id FROM ' . $tableName2 . ' where brand_id = "' . $brand_id . '" ';
                $getIds = $connection->fetchAll($getStoreId);
                $id = [];
                foreach ($getIds as $getId) {
                    $id[] = $getId['store_id'];
                }
                $results = array_diff($store_Id, $id);
                // $store_ids = array('2', '3', '4');
                foreach ($results as $result) {
                    $sql = 'insert into ' . $tableName2 . ' (brand_id, store_id) Values (' . $brand_id . ', "' . $result . '")';
                    $connection->query($sql);
                }
            }
            $brand_name_list[] = $brand_id;
        }
        //print_r($brand_name_list);
        if (count($brandsResults['value']) >= $top) {
            $newOffset = $offset + $top;
            // exit;
            $this->recursivelyAPICall($newOffset, $brand_name_list);
        } else {
            $this->brandsDisableCall($brand_name_list);
        }
    }

    public function brandsDisableCall($brand_name_list)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $options = [];
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('magetop_brand');
        $getNames = [];
        $selectNames = "Select brand_id FROM " . $tableName1;
        // $selectNames = "Select brand_id FROM " . $tableName1. " LIMIT ".$top. " offset ".$skip;
        $getValueLists = $connection->fetchAll($selectNames);
        foreach ($getValueLists as $getValueList) {
            $getNames[] = $getValueList['brand_id'];
        }
        $results = array_diff($getNames, $brand_name_list);
        foreach ($results as $result) {
            $sql = "Update " . $tableName1 . " Set status = 0 where brand_id = '$result'";
            $connection->query($sql);
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
