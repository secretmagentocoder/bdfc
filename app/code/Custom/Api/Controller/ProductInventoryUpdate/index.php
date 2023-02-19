<?php


namespace Custom\Api\Controller\ProductInventoryUpdate;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    protected $eavSetupFactory;

    protected $storeFactory;

    private $navConfigProvider;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreFactory $storeFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ConfigProvider $navConfigProvider,
        array $data = array()

    ) {
        $resultJsonFactory = $resultJsonFactory;
        $this->timezone = $timezone;
        $this->categoryFactory = $categoryFactory;
        $this->storeFactory = $storeFactory;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resourceConnection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->updateProductInventory($skip = 0, $limit = 50);
        // reindexAll
        $this->reindexAll();
        // flushCache
        $this->flushCache();
    }


    //recursively category api call
    private function updateProductInventory($skip, $limit)
    {

        
        $productData = $this->curlCall($skip, $limit);
        $this->processProductData($productData);
        if (count($productData) >= $limit) {
            $skip = $skip + $limit;
            echo "Procees Next Set";
            $this->updateProductInventory($skip, $limit);
        } else {
            exit;
        }
    }

    private function processProductData($productData)
    {
        ob_start();
        if (is_array($productData) && count($productData) > 0) {

            foreach ($productData as $product) {
                echo "<pre>";
                echo "processing SKU with NO " . $product['Item_No'];
                $this->updateProductData($product);
                usleep(100);
                flush();
                ob_flush();
            }
        }
    }

    private function updateProductData($product_data)
    {
        $pr_cartreservation_enable = "0";
        if (!empty($product_data)) {
            $is_in_stock = 'true';
            $sku = $product_data['Item_No'];
            $inventory = $product_data['Inventory_With_Threshold'];
            $stockQty=$this->getProductMaxSaleQty($sku);
            $baseUrl = $this->navConfigProvider->getBaseUrl();
            $ch = curl_init($baseUrl."default/rest/V1/products/" . $sku . "/stockItems/1");
            if ($inventory <= 0 || $inventory<= $stockQty) {
                $inventory = 0;
                $is_in_stock = 'false';
            }
          
            

            $payload = '{
                "product_sku": "' . $sku . '",
                "stockItem": {
                    "use_config_max_sale_qty":false,
                    "is_qty_decimal": false,
                    "is_in_stock": ' . $is_in_stock . ',
                    "qty": ' . $inventory . '
                }
            }';


            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $headers = array(
                "Accept: application/json",
                'Content-Type:application/json',
                "Authorization: Bearer xwpwzi25clerlcxlxblaxlk8tvwvwkx6",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            # Return response instead of printing.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            # Send request.
            $result = curl_exec($ch);
            var_dump($result);
            curl_close($ch);
            # Print response.
            echo "<pre>Done Proceesing </pre>";
        }
    }


    private function curlCall($skip, $limit)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/WebInventorywithThreshold?$format=application/json&$skip=' . $skip . '&$top=' . $limit;;
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
            return $response['value'];
        } else {
            return [];
        }
    }

    private function getProductMaxSaleQty($sku)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $sku = "'" . $sku . "'";
        $store_url = $host.'/Company(%27'.$company.'%27)/WebItemList?$format=application/json&$filter=No%20eq%20' . $sku;
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

        if (is_array($response) && count($response) > 0  && isset($response['value'][0]['Web_Minimum_Quantity'])) {
            return $response['value'][0]['Web_Minimum_Quantity'];
        } else {
            return 0;
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
