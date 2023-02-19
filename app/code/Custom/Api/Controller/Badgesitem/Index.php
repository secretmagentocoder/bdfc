<?php

namespace Custom\Api\Controller\Badgesitem;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\ProductFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    protected $eavSetupFactory;

    protected $productFactroy;

    private $navConfigProvider;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\Product\Action $action,
        ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->action = $action;
        $this->productFactroy = $productFactory;
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
                    $this->BadgeItemLinkFromBadge($product_sku);
                }             
            }
            // call recursive func
            if (count($category_data['value']) >= $top) {
                $newOffset = $offset + $top;
                $this->recursivelyAPICall($newOffset);
            }
        }
    }
 
    public function BadgeItemLinkFromBadge($product_sku)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company(%27'.$company.'%27)/BadgeItemLinkFromBadge?$format=application/json&$filter=Item_No%20eq%20%27' . $product_sku . '%27';
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
        $badgesResults = json_decode($response_data, TRUE);
        echo "<pre>";
        // print_r($badgesResults);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('catalog_product_entity');
        $tableName2 = $resource->getTableName('product_badges_item');
        $tableName3 = $resource->getTableName('catalog_product_entity_int');
        $tableName4 = $resource->getTableName('eav_attribute');
        $tableName5 = $resource->getTableName('product_badges');

        $getBadgesLists = [];
        foreach ($badgesResults['value'] as $key => $value) {
            $product = $objectManager->create('Magento\Catalog\Model\Product');
            $productSku = $value['Item_No'];
            $badge_id = $value['Badge_ID'];
            $badge_name = $value['Badge_Name'];
            $from = [' ', '-', '&', '$'];
            $to = ['_', '_', '_', '_'];
            $badge_slug = str_replace($from, $to, strtolower($badge_name));
            $badge_priority = $value['Badge_Priority'];
            $badge_start_date = $value['Badge_Start_Date'];
            $badge_end_date = $value['Badge_End_Date'];
            $badge_start_date = explode("T",$badge_start_date)[0];
            $badge_end_date = explode("T",$badge_end_date)[0];
            if ($product->getIdBySku($productSku)) {
                $getProductId = "Select entity_id, row_id FROM " . $tableName1 . " where sku = '$productSku'";
                $result1 = $connection->fetchAll($getProductId);
                $productId = $result1[0]['entity_id'];
                $productRawId = $result1[0]['row_id'];
                if ($badge_slug) {
                    $todayDate = $this->timezone->date()->format('Y-m-d');
                    if (($badge_start_date == '0001-01-01' && $badge_end_date >= $todayDate) || ($badge_start_date <= $todayDate && $badge_end_date == '0001-01-01') || ($badge_start_date <= $todayDate && $badge_end_date >= $todayDate)) {
                        $this->action->updateAttributes([$productId], [$badge_slug => 1], 0);
                    } else {
                        $this->action->updateAttributes([$productId], [$badge_slug => 0], 0);
                    }
                    $getAttributeId = "Select attribute_id FROM " . $tableName4 . " where attribute_code = '$badge_slug'";
                    $result2 = $connection->fetchAll($getAttributeId);
                    $getAttributeId = $result2[0]['attribute_id'];
                }
                $fields = [];
                $fields['row_id'] = $productRawId;
                $fields['attribute_id'] = $getAttributeId;
                $getBadgesLists[] = $fields;
            }
        }

        // for remove
        $all_api_badges = [];
        foreach ($badgesResults['value'] as $badges_item) {
            $BadgeName = $badges_item['Badge_Name'];
            $all_api_badges []= $BadgeName;
        }
        print_r($all_api_badges);

        $getBadgeSlugLists_query = "Select * FROM " . $tableName5;
        $getBadgeSlugLists = $connection->fetchAll($getBadgeSlugLists_query);
        $all_magento_badges = [];
        foreach ($getBadgeSlugLists as $getBadgeSlugList) {
            $badge_slug = $getBadgeSlugList['badge_slug'];
            $badge_name = $getBadgeSlugList['badge_name'];
            $all_magento_badges[] = $badge_name;

        }
        // print_r($all_magento_badges);

        $all_remove_badges = array_diff($all_magento_badges, $all_api_badges);
        // print_r($all_remove_badges);

        $getRemoveAttributeLists = [];
        foreach ($all_remove_badges as $value) {
            $badges_name = $value;
            $getAttributeId = "Select * FROM " . $tableName5 . " where badge_name = '$badges_name'";
            $result2 = $connection->fetchRow($getAttributeId);
            $getRemoveAttributeLists []= $result2['badge_slug'];
        }
        // print_r($getRemoveAttributeLists);

        foreach ($getRemoveAttributeLists as $getAttributeList) {
            $badge_slug = $getAttributeList;
            // 
            try{
                $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
                $product_id = $product->getId();
                
                $this->action->updateAttributes([$product_id], [$badge_slug => 0], 0);

                // echo $product_sku." - this product is updated";

            } catch (\Exception $e) {
                // $this->messageManager->addException($e, __('Something went wrong while saving the comment'));
                // echo $product_sku." - this sku not exist";
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
