<?php

namespace Custom\Api\Controller\ConfigurableProduct;

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
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->recursivelyAPICall();
        // reindexAll
        $this->reindexAll();

        // flushCache
        $this->flushCache();
    }

    public function recursivelyAPICall()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/api_product.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $response = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
        $sku = 'sku11'; 
        $product->setSku($sku);
        $product->setName('Grouped Product31');
        $product->setWeight(1);
        $product->setPrice(100);
        $product->setDescription('description'); 
        $product->setAttributeSetId(4); 
        $product->setStatus(1);
        $product->setVisibility(4); 
        $product->setTypeId('grouped'); 
        $product->setStoreId(1); 
        $product->setWebsiteIds(array(1)); 
        $product->setVisibility(4); 
        $product->setStockData(array(
        'use_config_manage_stock' => 0, 
        'manage_stock' => 1, 
        'min_sale_qty' => 1, 
        'max_sale_qty' => 2,
        'is_in_stock' => 1, 
        'qty' => 1000
        )
        );
        $product->save();

        $childrenIds = [164,1876,160,1878]; 
        $associated = [];
        $position = 0;
        foreach ($childrenIds as $productId)
        {
        $position++;
        $linkedProduct = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface')->getById($productId);
        $productLink = $objectManager->create('\Magento\Catalog\Api\Data\ProductLinkInterface');
        $productLink->setSku($product->getSku())
                    ->setLinkType('associated') 
                    ->setLinkedProductSku($linkedProduct->getSku()) 
                    ->setLinkedProductType($linkedProduct->getTypeId())
                    ->setPosition($position) 
                    ->getExtensionAttributes()
                    ->setQty(1);
        $associated[] = $productLink;
        }
        $product->setProductLinks($associated);
        $product->save();
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
