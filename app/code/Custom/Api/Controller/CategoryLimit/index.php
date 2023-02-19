<?php


namespace Custom\Api\Controller\CategoryLimit;

use Exception;
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
        $item_code = isset($_GET['item_code']) ? $_GET['item_code'] : "";

        $this->updateCategoryLimits();
    }


    //recursively category api call
    private function updateCategoryLimits()
    {

        echo "<pre>";
        header('Content-type: text/html; charset=utf-8');
        $categories = $this->curlCall();
        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];
        $arrivalStoreCategoriesLimit = $this->processCategoryLimit($categories, '1E');
        $departureStoreCategoriesLimit= $this->processCategoryLimit($categories,'2E');
        $homeDeliveryCategoriesLimit= $this->processCategoryLimit($categories,'8');
        $arrivalJSON=json_encode($arrivalStoreCategoriesLimit);
        $departureJSON = json_encode($departureStoreCategoriesLimit);
        $homeDeliveryJSON = json_encode($homeDeliveryCategoriesLimit);
        
        // var_dump($homeDeliveryCategoriesLimit);
        //For Arrival Website
        $sql = "UPDATE core_config_data  SET value = '". $arrivalJSON."' WHERE path = 'minmaxqtypercate/bssmmqpc/min_max_qty' and scope_id=2";
        $this->resourceConnection->query($sql);
      
        
        // For Departure Website
        $sql = "UPDATE core_config_data  SET value = '" . $departureJSON . "' WHERE path = 'minmaxqtypercate/bssmmqpc/min_max_qty' and scope_id=3";
        $this->resourceConnection->query($sql);
        //For Home Delivery Website
        $sql = "UPDATE core_config_data  SET value = '" . $homeDeliveryJSON . "' WHERE path = 'minmaxqtypercate/bssmmqpc/min_max_qty' and scope_id=4";
        $this->resourceConnection->query($sql);
        // $this->flushCache();
        echo "Done";
    }

    private function processCategoryLimit($categories,$storeCode)
    {
       
        $categoriesLimits=[];
        if (is_array($categories) && count($categories) > 0) {
            foreach ($categories as $category) {
                $categoriesLimits[random_int(23222333,34343434)]=$this->getCategoryLimit($category, $storeCode,0);
            }
            foreach ($categories as $category) {
                $categoriesLimits[random_int(23222333, 34343434)] = $this->getCategoryLimit($category, $storeCode,1);
            }
        }
        
        return $categoriesLimits;
    }

    private function getCategoryLimit($category, $storeCode,$customerGroup)
    {
        $return=[];
         if (!empty($category) && strpos($category['Store'], $storeCode) !== false) {
            $category_id = $category['Web_Category_ID'];
            $categoryData = $this->getCategoryId($category_id);
            $return['customer_group_id']=$customerGroup;
            $return['category_id'] = $categoryData;
            $return['min_sale_qty'] = 1;
            $return['max_sale_qty'] = $category['Web_Qty_Limit'];
            
        }
        return $return;
    }

    private function getCategoryId($categoryCode){
      
        $select_query = $this->resourceConnection->select()->from('catalog_category_entity', ['*'])->where('web_category_code = ?', $categoryCode);
        $categories_data = $this->resourceConnection->fetchRow($select_query);
        return isset($categories_data)? $categories_data['entity_id']:0;
    }


    private function curlCall()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/WebCategory?$format=application/json&$filter=Web_Qty_Limit%20gt%200';
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
