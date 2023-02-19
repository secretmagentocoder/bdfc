<?php

namespace Custom\Api\Controller\BadgesMultiSelect;

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
        $attribute_url = $host.'/Company(%27'.$company.'%27)/WebBadges?$format=application/json';
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
        $badgesResults = json_decode($response_data,TRUE);
        print_r($badgesResults);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $options = [];
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('eav_attribute_option'); //gives table name with prefix
        $tableName2 = $resource->getTableName('eav_attribute_option_value'); //gives table name with prefix
        foreach($badgesResults['value'] as $value){
            $badge_name = $value['Badge_Name'];
            $badge_value = str_replace(' ', '_', strtolower($value['Badge_Name'])).'_'.$value['Badge_ID'];
            $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')
                            ->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
            $query = $connection->select()->from('eav_attribute_option_value',['value'])->where('value = ?', $badge_name);
            $result1 = $connection->fetchRow($query);
            if($result1['value'] != $badge_name){
                $getAttribute = "Insert Into " . $tableName1 . " (attribute_id) Values (254)";
                $connection->query($getAttribute);
                $getlastid = "Select option_id FROM " . $tableName1 . " ORDER BY option_id DESC LIMIT 1";
                $result = $connection->fetchAll($getlastid);
                $option_id = $result[0]['option_id'];
                echo $option_id;
                echo $badge_name;
                $sql = "insert into " . $tableName2 . " (option_id, store_id, value) Values ($option_id, 0, '".$badge_name."')";
                $connection->query($sql);
            }else{
                echo $badge_name . " badge already exist "."<br/>";
            }
        }
	}
}
