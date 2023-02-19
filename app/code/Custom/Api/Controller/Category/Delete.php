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

class Delete extends \Magento\Framework\App\Action\Action {

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

        $api_categories = [];
        foreach ($category_data['value'] as $category_item) {
            $category_code = $category_item['Web_Category_Code'];
            $category_name = ucwords(strtolower($category_item['Category_Short_Description']));
            echo $category_name;
            echo "<br>";

            $category = $objectManager->create('Magento\Catalog\Model\Category');

            // for check if exist
            $select_query = $connection->select()->from('catalog_category_entity',['*'])->where('web_category_code = ?', $category_code);
            $categories_data = $connection->fetchRow($select_query);
            // print_r($categories_data);

            if(!empty($categories_data)){
                $entity_id = $categories_data['entity_id'];
                $web_category_code = $categories_data['web_category_code'];
                $api_categories[] = $entity_id;
                // $api_categories[] = $web_category_code;
            }
        }
        echo "<br>";
        echo "<br>";
        echo "API Category";
        echo "<br>";
        print_r($api_categories);
        echo "<br>";
        echo 'Count: '.count($api_categories);

        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()                              
            ->addAttributeToSelect('*');

        $parent_categories = ['1', '2', '2398', '2988', '2989', '2990', '2991', '2992', '2993', '2994'];
        $website_categories = [];
        foreach ($categories as $category){
            $entity_id = $category->getId();
            $web_category_code = $category->getWebCategoryCode();
            if (!in_array($entity_id, $parent_categories)) {
                $website_categories[] = $entity_id;
                // $website_categories[] = $web_category_code;
            }
        }
        echo "<br>";
        echo "<br>";
        echo "Magento Category";
        echo "<br>";
        print_r($website_categories);
        echo "<br>";
        echo 'Count: '.count($website_categories);

        $delete_categories = array_diff($website_categories,$api_categories);
        echo "<br>";
        echo "<br>";
        echo "Deleting Category";
        echo "<br>";
        print_r($delete_categories);
        echo "<br>";
        echo 'Count: '.count($delete_categories);

        foreach ($delete_categories as $value){
            $cat_id = $value;
            $category = $objectManager->create('Magento\Catalog\Model\Category');
            $category = $category->load($cat_id);
            $category->delete();

            echo $category_name." --deleted"."<br/>";
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
