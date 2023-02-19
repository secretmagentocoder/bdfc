<?php

namespace Custom\Api\Controller\CustomExciseProduct;

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
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        // $this->eavSetupFactory = $eavSetupFactory;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {

        // recursivelyWebCategoryLinkAPICall
        $this->recursivelyWebCategoryLinkAPICall($offset = 0);
    }

    // recursivelyWebCategoryLinkAPICall
    public function recursivelyWebCategoryLinkAPICall($offset)
    {
        $top = 1000;
        $skip = $offset;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        // $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

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
        echo '<pre>';
        $category_array = json_decode($response_data,TRUE);
        print_r($category_array);

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        if (!empty($category_array['value'])) {
            foreach ($category_array['value'] as $key => $value) {
                $item_no = $value['No'];
                $item_stores = $value['Web_Store_Code'];
                echo "<br>";
                echo "SKU : ".$item_no;

                // WebItemList
                $item_list_url = $host.'/Company('.'\''.$company.'\''.')/WebItemList?$format=application/json&$filter=No%20eq%20%27'.$item_no.'%27';
                $header = array('Content-Type:application/atom+xml');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $item_list_url,
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
                $item_data = curl_exec($curl);
                curl_close($curl);
                $item_array = json_decode($item_data,TRUE);
                // print_r($item_array);

                $product_group_code = '';
                if (!empty($item_array['value'])) {
                    $product_group_code = $item_array['value']['0']['Product_Group_Code'];
                }

                // product_group_code
                if (!empty($product_group_code)) {

                    // WebRetailProduct
                    $WebRetailProduct_url = $host.'/Company('.'\''.$company.'\''.')/WebRetailProduct?$format=application/json&$filter=Code%20eq%20%27'.$product_group_code.'%27';
                    $header = array('Content-Type:application/atom+xml');
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $WebRetailProduct_url,
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

                    if (!empty($response_array['value'])) {
                        foreach ($response_array['value'] as $key => $value) {
                            $item_category_code = $value['Item_Category_Code'];
                            $code = $value['Code'];
                            $description = $value['Description'];
                            $excise_applicable = $value['Excise_Applicable'];
                            echo '<br>';
                            echo "Product_Group_Code : ".$code;
                            echo '<br>';
                            echo "Excise_Applicable : ".$excise_applicable;
                            echo '<br>';

                            // addProductExciseCall
                            $this->addProductExciseCall($item_no, $excise_applicable);
                        }
                    }
                }
            }

            // call recursive func
            if (count($response_array['value']) >= $top) {
                $newOffset = $offset + $top;
                $this->recursivelyWebCategoryLinkAPICall($newOffset);
            }
        }

        // reindexAll
        $reindexAll = $this->reindexAll();

        // flushCache
        $flushCache = $this->flushCache();
    }

    // addProductExciseCall
    function addProductExciseCall($item_no, $excise_applicable)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        // $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        // get store id
        $sql = "Select * FROM `web_excise_duty_setup`";
        $result = $connection->fetchAll($sql);
        // print_r($result);
        $excise_store_ids = [];
        $add_excise_calculation_base = '';
        $vat_for_excise_duty = '';
        foreach ($result as $key => $value) {
            $excise_store_ids[] = $value['store_id'];
            $add_excise_calculation_base = $value['add_excise_calculation_base'];
            $vat_for_excise_duty = $value['vat_for_excise_duty'];
        }
        // print_r($excise_store_ids);
        $diff_store_ids = array_diff($store_ids, $excise_store_ids);
        // print_r($diff_store_ids);

        if (!empty($item_no)) {
            $product_sku = $item_no;

            try{
                $productModel = $objectManager->create('Magento\Catalog\Model\Product');
                $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
                $product_id = $product->getId();
                // echo $product->getName();

                if ($excise_applicable == true) {
                    // for excise_applicable true
                    if (!empty($diff_store_ids)) {
                        foreach ($diff_store_ids as $key => $value) {
                            $store_id = $value;
                            // $product->setStoreId($store_id);
                            // $product->setExciseDuty(false);
                            $product->addAttributeUpdate('excise_duty_price', 0, $store_id);
                            $product->addAttributeUpdate('excise_duty', false, $store_id);
                            $product->save();
                        }
                    }
                    if (!empty($excise_store_ids)) {
                        foreach ($excise_store_ids as $key => $value) {
                            $store_id = $value;
                            // $product->setStoreId($store_id);
                            // $product->setExciseDuty(true);

                            // for excise_duty_price
                            $productStore = $productModel->setStoreId($store_id)->load($product_id);
                            $product_price = $productStore->getFinalPrice();
                            $product_unit_price = $productStore->getUnitPrice();
                            $excise_duty_percent = 0;
                            $product_price_with_excise = 0;
                            if ($add_excise_calculation_base == 'Retail Price') {
                                $excise_duty_percent = 100;
                            }
                            $excise_duty_price = (($product_unit_price * $excise_duty_percent) / 100);
                            $excise_duty_price = number_format((float)$excise_duty_price, 2, '.', '');
                            $product_price_with_excise = $excise_duty_price + $product_unit_price;

                            $product->addAttributeUpdate('excise_duty_price', $excise_duty_price, $store_id);
                            $product->addAttributeUpdate('excise_duty', true, $store_id);
                            $product->addAttributeUpdate('price', $product_price_with_excise, $store_id);
                            $product->save();
                        }
                    }
                }else{
                    // for excise_applicable false
                    if (!empty($store_ids)) {
                        foreach ($store_ids as $key => $value) {
                            $store_id = $value;
                            // $product->setStoreId($store_id);
                            // $product->setExciseDuty(false);
                            $product->addAttributeUpdate('excise_duty_price', 0, $store_id);
                            $product->addAttributeUpdate('excise_duty', false, $store_id);
                            $product->save();
                        }
                    }
                }
                // $product->save();
                echo $product_sku." - this product is updated";

            } catch (\Exception $e) {
                // $this->messageManager->addException($e, __('Something went wrong while saving the comment'));
                echo $product_sku." - this sku not exist";
            }
        }
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
