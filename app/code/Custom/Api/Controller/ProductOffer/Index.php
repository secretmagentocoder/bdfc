<?php

namespace Custom\Api\Controller\ProductOffer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;

	protected $resourceConnection;

    protected $eavSetupFactory;


	public function __construct(
        Context $context, 
        JsonFactory $resultJsonFactory, 
        ResourceConnection $resourceConnection, 
        // EavSetupFactory $eavSetupFactory,
        array $data = array()
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        // $this->eavSetupFactory = $eavSetupFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $productModel = $objectManager->create('Magento\Catalog\Model\Product');
        $item = $objectManager->create('Magento\Catalog\Model\Product');
        $rules = $objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules = $rules->getCollection();

        $prodColl = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        
        $item_sku = @$_GET['item_sku'];
        if (isset($item_sku) && !empty($item_sku)) {
            $collection = $prodColl->addAttributeToSelect('*')->addAttributeToFilter('sku', ['eq'=>$item_sku])->load();
        }else{
            $collection = $prodColl->addAttributeToSelect('*')->load();
        }

        echo '<pre>';
        // print_r($collection->getData());

        $count = 1;
        foreach ($collection->getData() as $key => $value) {
            $product_id = $value['entity_id'];
            $type_id = $value['type_id'];
            if ($type_id == "simple") {
                try {
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                    $item->setProduct($product);
                    $product_sku = $product->getSku();
                    echo 'SKU: '.$product_sku;
                    echo '<br>';
                    
                    $store_ids = array('2', '3', '4');
                    foreach ($store_ids as $key => $store_id) {
                        $product->addAttributeUpdate('product_offer', '', $store_id);
                        $product->save();
                    }

                    $store_ids = [];
                    $product_all_offers = [];
                    foreach ($rules as $rule) {
                        $is_active = $rule->getIsActive();
                        $website_ids = $rule->getWebsiteIds();

                        if (isset($is_active) && $is_active == '1') {
                            if ($rule->getActions()->validate($item)) {
                                $product_label = $rule->getName();
                                $product_all_offers[] = $product_label;
                                $store_ids = $website_ids;

                                foreach ($store_ids as $key => $store_id) {
                                    $product_offers = [];
                                    $productModel = $objectManager->create('Magento\Catalog\Model\Product');
                                    $productStore = $productModel->setStoreId($store_id)->load($product_id);
                                    $product_offer = $productStore->getProductOffer();
                                    $product_offers = unserialize($product_offer);
                                    $product_offers []= $product_label;
                                    $product_offer = serialize($product_offers);

                                    $product->addAttributeUpdate('product_offer', $product_offer, $store_id);
                                    $product->save();
                                }
                            }
                        }
                    }
                    echo 'Offers: ';
                    print_r($product_all_offers);

                    /*$product_offer = serialize($product_all_offers);
                    foreach ($store_ids as $key => $store_id) {
                        $product->addAttributeUpdate('product_offer', $product_offer, $store_id);
                        $product->save();
                    }*/

                    echo $product_sku." this sku has been updated";
                    echo '<br>';
                    echo '<br>';
                } catch (Exception $e) {
                    echo $product_sku." this sku not found";
                    echo '<br>';
                    echo '<br>';
                }

                if ($count == 5) {
                   // break;
                }
                $count++;
            }
        }


        // reindexAll
        // $reindexAll = $this->reindexAll();

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
