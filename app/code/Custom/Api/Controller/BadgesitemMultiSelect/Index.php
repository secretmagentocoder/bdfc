<?php

namespace Custom\Api\Controller\BadgesitemMultiSelect;

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
        ConfigProvider $navConfigProvider,
        array $data = array()
    )
    {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->action = $action;
        $this->storeManager = $storeManager;
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
        $attribute_url = $host.'/Company(%27'.$company.'%27)/WebBadgesItem?$format=application/json';;
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
        $badgesResults = json_decode($response_data,TRUE);
        print_r($badgesResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('catalog_product_entity');
        $tableName2 = $resource->getTableName('eav_attribute_option_value');
        $connection->query($sql);
        $option=[];
        foreach($badgesResults['value'] as $value){
            $product = $objectManager->create('Magento\Catalog\Model\Product');
            $productSku = $value['Item_No'];
            $badge_name = $value['Badge_Name'];
            if($product->getIdBySku($productSku)){
                $getAttributeOption = "Select option_id FROM " . $tableName2 . " where value = '".$badge_name."'";
                $result = $connection->fetchAll($getAttributeOption);
                $getoptionid = $result[0]['option_id'];
                if(!isset($option[$productSku])){
                    $option[$productSku]=[];
                }
                $option[$productSku][]=$getoptionid;
            }
        }
        // echo '<pre>';
        // print_r($option);
        foreach($badgesResults['value'] as $value){
            $product = $objectManager->create('Magento\Catalog\Model\Product');
            $productSku = $value['Item_No'];
            $badge_priority = $value['Badge_Priority'];
            $badge_start_date = explode("T",$value['Badge_Start_Date'])[0];
            $badge_end_date = explode("T",$value['Badge_End_Date'])[0];
            if($product->getIdBySku($productSku)){
                $getproduct = "Select entity_id FROM " . $tableName1 . " where sku = $productSku";
                $result = $connection->fetchAll($getproduct);
                $getproductid = $result[0]['entity_id'];
                $store = $this->storeManager->getStore(); 
                $storeId = $store->getId();  // Get Store ID
                $getoptionid = null;
                foreach($option as $key => $value){
                    if($key == $productSku){
                        $getoptionid = implode(',',$value);
                            $this->action->updateAttributes([$getproductid],['web_badge' => $getoptionid],$storeId);
                    }
                }
            }
        }
	}
}

// $sql = "insert into " . $tableName3 . 
//         "(product_id, option_id, store_id, product_sku, badge_name, badge_start_date, badge_end_date, badge_priority) 
//         Values 
//         ($getproductid, $getoptionid, $storeId, $productSku, '".$badge_name."', $badge_start_date, $badge_end_date, $badge_priority)";
// $connection->query($sql);

