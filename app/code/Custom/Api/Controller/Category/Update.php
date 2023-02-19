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

class Update extends \Magento\Framework\App\Action\Action {

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
        )
    {
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
        $category_data = json_decode($response_data,TRUE);
        // echo '<pre>';
        // print_r($category_data);

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 

        $dbUrlKeys = [];
        if(!empty($category_data['value'])){
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

                $to = [" - "," / ","/"," & ",' ',"'"];
                $from = ['-',"-",'-','-','-',""];
                $urlKey = str_replace($to, $from, strtolower($category_item['Category_Short_Description']));

                $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
                $parentCategory = $objectManager->create('Magento\Catalog\Model\Category')->load($parentId);

                // for check if exist
                $select_query = $connection->select()->from('catalog_category_entity',['*'])->where('web_category_code = ?', $category_code);
                $categories_data = $connection->fetchRow($select_query);
                // print_r($categories_data);

                if(!empty($categories_data)){
                    // print_r($category_name);
                    $id = $categories_data['entity_id'];
                    $category = $objectManager->get('Magento\Catalog\Model\Category')->load($id);
                    $cat_name = $category->getName();
                    $cat_id = $category->getId();

                    // for update name
                    $query = $connection->select()->from('catalog_category_entity',['*'])->where('entity_id = ?', $cat_id);
                    $result = $connection->fetchRow($query);
                    // print_r($result);
                    $category_name_ = str_replace("'", "''", $category_name);
                    $meta_title_ = str_replace("'", "''", $meta_title);
                    $entity_id = $result['entity_id'];
                    $parent_id = $result['parent_id'];
                    $row_id = $result['row_id'];

                    if (!empty($row_id)) {
                        // for update parent id
                        $query = $connection->select()->from('catalog_category_entity',['*'])->where('entity_id = ?', $parent_id);
                        $result = $connection->fetchRow($query);
                        $parent_entity_id = $result['entity_id'];              
                        $parent_web_category_code = $result['web_category_code']   ;

                        if($parent_category_name != $parent_web_category_code){ 
                            $parent_entity_id = '';                  
                            $parent_path_ = '';             
                            if(!empty($parent_category_name)){
                                $query = $connection->select()->from('catalog_category_entity',['entity_id','path'])->where('web_category_code = ?', $parent_category_name);
                                $result = $connection->fetchRow($query);
                                $parent_entity_id = $result['entity_id'];
                                $parent_path = $result['path'];
                                $parent_path_ = $parent_path.'/'.$entity_id;
                            }else{
                                $parent_entity_id = '2';
                                $parent_path = '1/2';
                                $parent_path_ = $parent_path.'/'.$entity_id;
                            }
                            // $category->setParentId($parent_entity_id);
                            // $category->setPath($parent_path_);
                            $category->move($parent_entity_id, null);
                            $category->save();


                            $stores = explode("|", $storeIds);
                            foreach($stores as $store){
                                $tableName1 = $resource->getTableName('web_store_num');
                                $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                                $result = $connection->fetchAll($getlastid);
                                $store_Id = $result[0]['store_id'];
                                if(in_array($store_Id, $store_ids)){
                                    $query = $connection->select()->from('catalog_category_entity_varchar',['*'])->where('row_id = ?', $row_id)->where('attribute_id = ?', '125')->where('store_Id = ?', $store_Id);
                                    $result = $connection->fetchRow($query);
                                    $cat_path = $result['value'];
                                    // for url_rewrite
                                    $entity_type = 'category';
                                    $request_path = $cat_path.'.html';
                                    $target_path = 'catalog/category/view/id/'.$entity_id;
                                    $redirect_type = '301';
                                    $store_id = $store_Id;

                                    $sql6 = $connection->select()->from('url_rewrite',['*'])->where('entity_id = ?', $entity_id)->where('request_path = ?', $request_path)->where('store_Id = ?', $store_Id);
                                    $result_ = $connection->fetchRow($sql6);
                                    if (!empty($result_)) {
                                        $url_rewrite_id = $result_['url_rewrite_id'];
                                        $sql7 = "DELETE from `url_rewrite` WHERE `url_rewrite_id` = ".$url_rewrite_id;
                                        $connection->query($sql7);
                                    }

                                    $sql5 = "insert into `url_rewrite` (entity_type, entity_id, request_path, target_path, redirect_type, store_id) Values ('".$entity_type."', '".$entity_id."', '".$request_path."', '".$target_path."', '".$redirect_type."', '".$store_id."')";
                                    $connection->query($sql5);
                                }
                            }
                        }

                    }

                    // for enable/disable
                    $stores = explode("|", $storeIds);
                    $results = array_diff($storeLists,$stores);
                    if($results){
                        foreach($results as $store){
                            $tableName1 = $resource->getTableName('web_store_num');
                            $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                            $result = $connection->fetchAll($getlastid);
                            $store_Id = $result[0]['store_id'];
                            if(in_array($store_Id, $store_ids)){
                                $category->setStoreId($store_Id);
                                $category->setIsActive(false);
                                // $sql3 = "UPDATE `catalog_category_entity_int` SET `value` = '0' WHERE `row_id` = '".$row_id."' AND `store_Id` = '".$store_Id."' AND `attribute_id` = '46'";
                                // $connection->query($sql3);
                            }
                            $category->save();
                        }
                    }
                    foreach($stores as $store){
                        $tableName1 = $resource->getTableName('web_store_num');
                        $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                        $result = $connection->fetchAll($getlastid);
                        $store_Id = $result[0]['store_id'];
                        if(in_array($store_Id, $store_ids)){
                            $start = explode("T",$category_item['Online_From'])[0];
                            $end = explode("T",$category_item['End_Date'])[0];
                            $todayDate = $this->timezone->date()->format('Y-m-d');
                            // if($start <= $todayDate && $start != '0001-01-01' && ($end >= $todayDate || $end == '0001-01-01')){
                            // if(($start <= $todayDate || $start == '0001-01-01') && ($end >= $todayDate || $end == '0001-01-01') && ($start != '0001-01-01' && $end != '0001-01-01')){
                            if(($start == '0001-01-01' && $end >= $todayDate) || ($start <= $todayDate && $end == '0001-01-01') || ($start <= $todayDate && $end >= $todayDate)){
                                $category->setStoreId($store_Id);
                                $category->setIsActive(true);
                                // $sql4 = "UPDATE `catalog_category_entity_int` SET `value` = '1' WHERE `row_id` = '".$row_id."' AND `store_Id` = '".$store_Id."' AND `attribute_id` = '46'";
                                // $connection->query($sql4);
                            }else{
                                $category->setStoreId($store_Id);
                                $category->setIsActive(false);
                                // $sql4 = "UPDATE `catalog_category_entity_int` SET `value` = '0' WHERE `row_id` = '".$row_id."' AND `store_Id` = '".$store_Id."' AND `attribute_id` = '46'";
                                // $connection->query($sql4);
                            }
                            $category->save();
                        }
                    }

                    if ($cat_hidden == true) {   
                        foreach($stores as $store){
                            $tableName1 = $resource->getTableName('web_store_num');
                            $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                            $result = $connection->fetchAll($getlastid);
                            $store_Id = $result[0]['store_id'];
                            if(in_array($store_Id, $store_ids)){
                                $category->setStoreId($store_Id);
                                $category->setIsActive(false);
                                // $sql4 = "UPDATE `catalog_category_entity_int` SET `value` = '0' WHERE `row_id` = '".$row_id."' AND `store_Id` = '".$store_Id."' AND `attribute_id` = '46'";
                                // $connection->query($sql4);
                            }
                            $category->save();
                        }
                    }

                    // $category->save();

                    if (!empty($row_id)) {
                        $sql = "UPDATE `catalog_category_entity_varchar` SET `value` = '".$category_name_."' WHERE `row_id` = '".$row_id."' AND `attribute_id` = '45'";
                        $connection->query($sql);

                        $sql2 = "UPDATE `catalog_category_entity_varchar` SET `value` = '".$meta_title_."' WHERE `row_id` = '".$row_id."' AND `attribute_id` = '49'";
                        $connection->query($sql2);

                        $sql3 = "UPDATE `catalog_category_entity_text` SET `value` = '".$meta_keywords."' WHERE `row_id` = '".$row_id."' AND `attribute_id` = '50'";
                        $connection->query($sql3);

                        $sql4 = "UPDATE `catalog_category_entity_text` SET `value` = '".$meta_description."' WHERE `row_id` = '".$row_id."' AND `attribute_id` = '51'";
                        $connection->query($sql4);

                        $sql5 = "UPDATE `catalog_category_entity` SET `position` = '".$priority."', `start_date` = '".$start_date."', `end_date` ='".$end_date."' WHERE `row_id` = '".$row_id."'";
                        $connection->query($sql5);
                    }

                    echo $category_name." --already exist "."<br/>";
                    echo $category_name." --updated"."<br/>";
                }
            }
        }
        
        // reindexAll
        $reindexAll = $this->reindexAll();

        // flushCache
        $flushCache = $this->flushCache();
    }

    // reindexAll
    public function reindexAll() {
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
    public function flushCache(){
        // php bin/magento c:f

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
        $_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
        $_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
        $types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
        foreach ($types as $type) {
            $_cacheTypeList->cleanType($type);
        }
        foreach ($_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

}
