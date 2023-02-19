<?php

namespace Custom\Api\Controller\Inventory;

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

    protected $stockRegistry;

    protected $getSalableQuantityDataBySku;

    private $navConfigProvider;

    public function __construct(
        Context $context, 
        JsonFactory $resultJsonFactory, 
        ResourceConnection $resourceConnection,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        ConfigProvider $navConfigProvider,
        array $data = array()
        )
    {
        $this->stockRegistry = $stockRegistry;
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = array();
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company(%27'.$company.'%27)/ItemWiseInventory?$format=application/json';
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
        echo '<pre>';
        $productResults = json_decode($response_data,TRUE);
        print_r($productResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach($productResults['value'] as $key => $value){
            $product = $objectManager->get('Magento\Catalog\Model\Product');
            // $sku = '113336';
            // $qty = '15';
            $sku = $value['Item_No'];
            $qty = $value['Inventory_For_Web'];
            // $Store_Code = $value['Store_Code'];
            if($product->getIdBySku($sku)) {
                // if($Store_Code == 'DEPARTURES'){
    
                // }elseif($Store_Code == 'DEPARTURES'){

                // }
                $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                $stockItem->setQty($qty);
                $stockItem->setStockData(
                    array(
                        'use_config_manage_stock' => 0, 
                        'manage_stock' => 1, 
                        'min_sale_qty' => 1, 
                        'max_sale_qty' => 2, 
                        'is_in_stock' => 1,
                        'qty' => $qty
                    )
                );; 
                // $stockItem->setIsInStock((bool)$qty); 
                $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
            }
        }
	}
}
