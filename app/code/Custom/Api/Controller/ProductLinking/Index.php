<?php

namespace Custom\Api\Controller\ProductLinking;

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
        $item_sku = @$_GET['item_sku'];
        $baseUrl = $this->navConfigProvider->getBaseUrl();
        if ($item_sku) {
            // getProduct
            $getProduct = $baseUrl.'customapi/product/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $getProduct,
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
            echo "string";
            print_r($response_data);

            // get Configurable Product
            $getConfigProduct = $baseUrl.'default/customapi/configurableproduct/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $getConfigProduct,
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
            echo "string";
            print_r($response_data);

            // getProductCategory
            $getProductCategory = $baseUrl.'customapi/productcategory/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $getProductCategory,
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
            echo "string";
            print_r($response_data);

            // getProductSalePrice
            $getSalePrice = $baseUrl.'default/customapi/saleprice/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $getSalePrice,
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
            echo "string";
            print_r($response_data);

            // vatlinkedproduct
            $vatlinkedproduct_url = $baseUrl.'customapi/vatlinkedproduct/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $vatlinkedproduct_url,
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
            echo "string";
            print_r($response_data);

            // customexciseproduct
            $customexciseproduct_url = $baseUrl.'customapi/customexciseproduct/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $customexciseproduct_url,
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
            echo "string";
            print_r($response_data);

            // customallowanceproduct
            $customallowanceproduct_url = $baseUrl.'customapi/customallowanceproduct/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $customallowanceproduct_url,
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
            echo "string";
            print_r($response_data);

            // attributevaluemapping
            $attributeValueMapping = $baseUrl.'customapi/attributevaluemapping/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $attributeValueMapping,
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
            echo "string";
            print_r($response_data);
            
            // badgesitem
            $badgesitem = $baseUrl.'customapi/badgesitem/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $badgesitem,
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
            echo "string";
            print_r($response_data);

            // itembrandlinklist
            $itembrandlinklist = $baseUrl.'customapi/itembrandlinklist/index?item_sku='.$item_sku;  
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $itembrandlinklist,
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
            echo "string";
            print_r($response_data);

        }

        // reindexAll
        $this->reindexAll();

        // // flushCache
        // $this->flushCache();
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
