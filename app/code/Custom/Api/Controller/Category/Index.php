<?php

namespace Custom\Api\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Store\Model\StoreFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;

    protected $resourceConnection;

    protected $eavSetupFactory;

    protected $storeFactory;

    private $navConfigProvider;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection,
        StoreFactory $storeFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $this->timezone = $timezone;
        $this->categoryFactory = $categoryFactory;
        $this->storeFactory = $storeFactory;
        $resourceConnection = $resourceConnection;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $category_url = $host.'/Company(%27'.$company.'%27)/WebCategory?$format=application/json';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $category_url,
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
        $category_data = json_decode($response_data, TRUE);
        // echo '<pre>';
        // print_r($category_data);

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        foreach ($category_data['value'] as $category_item) {
            $category_code = $category_item['Web_Category_Code'];
            $category_id = $category_item['Web_Category_ID'];
            $priority = $category_item['Priority'];
            $cat_hidden = $category_item['Hidden'];
            $storeIds = $category_item['Store'];
            $category_name = ucwords(strtolower($category_item['Category_Short_Description']));
            $parent_category_name = $category_item['Parent_Category'];

            $Online_From = $category_item['Online_From'];
            $End_Date = $category_item['End_Date'];
            $start_date = str_replace('T', ' ', $Online_From);
            $end_date = str_replace('T', ' ', $End_Date);

            $meta_title = $category_item['Page_Title'];
            $meta_description = $category_item['Page_Description'];
            $meta_keywords = '';
            if (empty($meta_title)) {
                $meta_title = $category_name;
            }

            $to = [" - ", " / ", "/", " & ", ' ', "'"];
            $from = ['-', "-", '-', '-', '-', ""];
            $urlKey = str_replace($to, $from, strtolower($category_item['Category_Short_Description']));

            $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $parentCategory = $objectManager->create('Magento\Catalog\Model\Category')->load($parentId);
            $category = $objectManager->create('Magento\Catalog\Model\Category');

            // for check if exist
            // $categories = $this->categoryFactory->create()->getCollection()->addAttributeToFilter('url_key', $urlKey)->addAttributeToSelect(['entity_id','name']);

            $select_query = $connection->select()->from('catalog_category_entity', ['*'])->where('web_category_code = ?', $category_code);
            $categories_data = $connection->fetchRow($select_query);
            // print_r($categories_data);

            if (empty($categories_data)) {
                $category->setName($category_name);
                $category->setWebCategoryCode($category_code);
                $category->setPosition($priority);
                $category->setCustomAttributes([
                    'meta_title' => $meta_title,
                    'meta_keywords' => $meta_keywords,
                    'meta_description' => $meta_description,
                    'url_key' => $urlKey,
                ]);

                $category->setStartDate($start_date);
                $category->setEndDate($end_date);

                if ($parent_category_name) {
                    $query = $connection->select()->from('catalog_category_entity', ['entity_id', 'path'])->where('web_category_code = ?', $parent_category_name);
                    $result = $connection->fetchRow($query);
                    $category->setParentId($result['entity_id']);
                    $path = $result['path'];
                    $category->setPath($path);
                } else {
                    $path = $parentCategory->getPath() . '/' . '2';
                    $category->setParentId(2);
                    $category->setPath($path);
                    // $category->setIncludeInMenu(false);
                }

                // we Get Store ID
                $stores = explode("|", $storeIds);
                $results = array_diff($storeLists, $stores);
                if ($results) {
                    foreach ($results as $store) {
                        $tableName1 = $resource->getTableName('web_store_num');
                        $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                        $result = $connection->fetchAll($getlastid);
                        $store_Id = $result[0]['store_id'];
                        if (in_array($store_Id, $store_ids)) {
                            $category->setStoreId($store_Id);
                            $category->setIsActive(false);
                            $category->save();
                        }
                    }
                }
                foreach ($stores as $store) {
                    $tableName1 = $resource->getTableName('web_store_num');
                    $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                    $result = $connection->fetchAll($getlastid);
                    $store_Id = $result[0]['store_id'];
                    if (in_array($store_Id, $store_ids)) {
                        $category->setStoreId($store_Id);
                        $start = explode("T", $category_item['Online_From'])[0];
                        $end = explode("T", $category_item['End_Date'])[0];
                        $todayDate = $this->timezone->date()->format('Y-m-d');
                        // if($start <= $todayDate && $start != '0001-01-01' && ($end >= $todayDate || $end == '0001-01-01')){
                        if (($start == '0001-01-01' && $end >= $todayDate) || ($start <= $todayDate && $end == '0001-01-01') || ($start <= $todayDate && $end >= $todayDate)) {
                            $category->setIsActive(true);
                        } else {
                            $category->setIsActive(false);
                        }
                        $category->save();
                    }
                }
                if ($cat_hidden == true) {
                    foreach ($stores as $store) {
                        $tableName1 = $resource->getTableName('web_store_num');
                        $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                        $result = $connection->fetchAll($getlastid);
                        $store_Id = $result[0]['store_id'];
                        if (in_array($store_Id, $store_ids)) {
                            $category->setStoreId($store_Id);
                            $category->setIsActive(false);
                        }
                        $category->save();
                    }
                }

                echo $category_name . " --added " . "<br/>";
            } else {
                echo $category_name . " --already exist " . "<br/>";
            }
        }

        // reindexAll
        $reindexAll = $this->reindexAll();

        // flushCache
        $flushCache = $this->flushCache();
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
