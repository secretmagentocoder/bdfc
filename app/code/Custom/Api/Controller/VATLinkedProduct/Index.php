<?php

namespace Custom\Api\Controller\VATLinkedProduct;

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

        // WebCategoryLink
        $item_sku = $_GET['item_sku']??0;
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
        $response_array = json_decode($response_data,TRUE);
        print_r($response_array);

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        if (!empty($response_array['value'])) {
            foreach ($response_array['value'] as $key => $value) {
                $item_no = $value['No'];
                $item_stores = $value['Web_Store_Code'];
                echo "<br>";
                echo "SKU : ".$item_no;

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

                if (!empty($item_array['value'])) {
                    $VAT_Prod_Posting_Group = $item_array['value']['0']['VAT_Prod_Posting_Group'];
                    $VAT_Prod_Posting_Group;
                }

                // we Get Store ID
                $stores = explode("|", $item_stores);
                foreach($stores as $store){
                    $tableName1 = $resource->getTableName('web_store_num');
                    $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$store'";
                    $result = $connection->fetchAll($getlastid);
                    
                    if (!empty($result[0]['store_id'])) {

                        $store_Id = $result[0]['store_id'];
                        if(in_array($store_Id, $store_ids)){
                            
                            $store_url = $host.'/Company('.'\''.$company.'\''.')/WebStore?$format=application/json&$filter=No%20eq%20%27'.$store.'%27';
                            $header = array('Content-Type:application/atom+xml');
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $store_url,
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
                            $store_data = curl_exec($curl);
                            curl_close($curl);
                            $store_array = json_decode($store_data,TRUE);
                            // print_r($store_array);
                            
                            if (!empty($store_array['value'])) {
                                $Store_VAT_Bus_Post_Gr = $store_array['value']['0']['Store_VAT_Bus_Post_Gr'];
                                $Store_VAT_Bus_Post_Gr;
                            }
                        }

                        echo '<br>';
                        echo $product_sku = $item_no;
                        echo '<br>';
                        echo $store_id = $store_Id;
                        echo '<br>';
                        echo $tax_class = $Store_VAT_Bus_Post_Gr.' - '.$VAT_Prod_Posting_Group;
                        echo '<br>';

                        try{
                            $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
                            $product_id = $product->getId();
                            // echo $product->getName();

                            // for get tax class id
                            $query = $connection->select()->from('tax_class',['*'])->where('class_name = ?', $tax_class);
                            $results = $connection->fetchRow($query);
                            // print_r($results);
                            if(!empty($results)){
                                $product_tax_class_id = $results['class_id'];

                                // $product->setStoreId($store_id);
                                // $product->setTaxClassId($product_tax_class_id);
                                $product->addAttributeUpdate('tax_class_id', $product_tax_class_id, $store_id);
                                $product->save();
                            }else{
                                $product_tax_class_id = 0;

                                // $product->setStoreId($store_id);
                                // $product->setTaxClassId(0);
                                $product->addAttributeUpdate('tax_class_id', $product_tax_class_id, $store_id);
                                $product->save();
                            }
                            echo $product_sku." this sku has been updated";

                        } catch (\Exception $e) {
                            // $this->messageManager->addException($e, __('Something went wrong while saving the comment'));
                            echo $product_sku." this sku not exist";
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
