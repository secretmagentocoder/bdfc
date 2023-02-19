<?php

namespace Custom\Api\Controller\CustomAllowanceProduct;

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
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

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
                    $this->WebItemListAPICall($product_sku);
                }             
            }
            // call recursive func
            if (count($category_data['value']) >= $top) {
                $newOffset = $offset + $top;
                $this->recursivelyWebCategoryLinkAPICall($newOffset);
            }
        }


        // reindexAll
        // $reindexAll = $this->reindexAll();
        
        // flushCache
        $flushCache = $this->flushCache();
    }

    // WebItemListAPICall
    public function WebItemListAPICall($product_sku)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        // WebItemList
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $WebItemList_url = $host.'/Company('.'\''.$company.'\''.')/WebItemList?$format=application/json&$filter=No%20eq%20%27'.$product_sku.'%27';
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $WebItemList_url,
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
        $product_data = json_decode($response_data,TRUE);
        echo '<pre>';
        // print_r($product_data);

        if (!empty($product_data['value'])) {
            foreach ($product_data['value'] as $product_item) {
                $product_sku = $product_item['No'];
                $custom_category = $product_item['Custom_Category'];
                $qty_per_custom_uom = $product_item['Qty_Per_Custom_UOM'];

                if (!empty($custom_category)) {
                    // for add/update custom_category
                    $query = "SELECT * FROM `web_custom_allowence_category` WHERE `category_code` = '".$custom_category."'";
                    $results = $connection->fetchAll($query);
                    print_r($results);
                    foreach ($results as $value) {
                        $option_id = $value['id'];
                        $store_id = $value['store_id'];

                        $custom_store_ids = [];
                        $custom_store_ids []= $store_id;
                        $diff_store_ids = array_diff($store_ids, $custom_store_ids);
                        // print_r($diff_store_ids);

                        try{
                            $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
                            $product_id = $product->getId();

                            // for custom_category true
                            if (!empty($diff_store_ids)) {
                                foreach ($diff_store_ids as $key => $value) {
                                    $store_id = $value;
                                    $product->addAttributeUpdate('custom_allowence_category', '', $store_id);
                                    $product->addAttributeUpdate('qty_per_custom_uom', '', $store_id);
                                    $product->save();
                                }
                            }
                            if (!empty($custom_store_ids)) {
                                foreach ($custom_store_ids as $key => $value) {
                                    $store_id = $value;
                                    $product->addAttributeUpdate('custom_allowence_category', $option_id, $store_id);
                                    $product->addAttributeUpdate('qty_per_custom_uom', $qty_per_custom_uom, $store_id);
                                    $product->save();
                                }
                            }
                            // $product->save();
                            echo $product_sku." - this product is updated";

                        } catch (\Exception $e) {
                            // $this->messageManager->addException($e, __('Something went wrong while saving the comment'));
                            echo $product_sku." - this sku not exist";
                        }
                    }
                }else{
                    // for remove custom_category
                    try{
                        $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
                        $product_id = $product->getId();
                        
                        // for custom_category false
                        if (!empty($store_ids)) {
                            foreach ($store_ids as $key => $value) {
                                $store_id = $value;
                                $product->addAttributeUpdate('custom_allowence_category', '', $store_id);
                                $product->addAttributeUpdate('qty_per_custom_uom', '', $store_id);
                                $product->save();
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
