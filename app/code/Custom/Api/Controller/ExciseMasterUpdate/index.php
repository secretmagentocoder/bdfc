<?php

namespace Custom\Api\Controller\ExciseMasterUpdate;

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

    protected $exciseCategories;

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
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        $this->navConfigProvider = $navConfigProvider;
        $this->exciseCategories = [];
        parent::__construct($context);
    }

    public function execute()
    {
        $this->recursivelyAPICall();
        // reindexAll
        // $this->reindexAll();

    }
    private function setExciseCategories()
    {
        $categoriesData = $this->curlCallExciseData('');
        foreach ($categoriesData as $category) {
            if (isset(($category['Code']))) {
                array_push($this->exciseCategories, $category['Code']);
            }
        }
        return  $this->exciseCategories;
    }

    public function recursivelyAPICall($offset = 0)
    {  
        // WebCategoryLink
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $product_group_code = $_GET['product_group_code']??0;
        if (isset($product_group_code) && !empty($product_group_code)) {
            $url = $host.'/Company('.'\''.$company.'\''.')/WebItemList?$format=application/json&$filter=Product_Group_Code%20eq%20%27' . $product_group_code . '%27';
        } else {
            echo "Product Group  is missing. Please check Product group";
            die;
        }
        $productGroupData=$this->curlCallExciseData($product_group_code);
       
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
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
        $products = json_decode($response_data, TRUE);
        echo '<pre>';
        // print_r($category_data);

        if (!empty($products['value']) && is_array($productGroupData) && isset($productGroupData['Excise_Applicable'])) {
            foreach ($products['value'] as $product) {
                $sku = $product['No'];
                echo '<br>';
                echo 'SKU : ' . $sku;
                echo '<br>';
                if (!empty($sku)) {
                    $this->updateProduct($sku,$product, $productGroupData['Excise_Applicable']);
                }
            }
           
        }
    }

    private function updateProduct($sku,$productAPIData,$exciseApplicable){
        
        $product = $this->productFactory->create();
        $product->load($product->getIdBySku($sku));
        // $product->loadByAttribute('sku', $sku);

        $unitPrice= $product->getUnitPrice();
        if($exciseApplicable==1){
            $unitPrice= $unitPrice*2;
        }
     
        $product->setPrice($unitPrice);
        $product->setExciseDuty($exciseApplicable);
        $product->save();

        
    }


    private function curlCallExciseData($product_group_code)
    {
        $product_group_code = "'" . $product_group_code . "'";
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/WebRetailProduct?$format=application/json&$filter=Code%20eq%20'. $product_group_code;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $store_url,
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
        $response = json_decode($response_data, true);
        if (is_array($response) && count($response) > 0) {
            return $response['value'][0];
        } else {
            return [];
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
