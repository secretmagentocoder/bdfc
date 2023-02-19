<?php

namespace Custom\Api\Controller\Currency;

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
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $post = $this->getRequest()->getPostValue();
        $response = array();
        $currency_url = $host.'/Company('.'\''.$company.'\''.')/WebCurrency?$format=application/json';
        $header = array('Content-Type:application/atom+xml');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $currency_url,
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        $currency_ids_arr = [];
        foreach ($response_array['value'] as $key => $value) {
            echo $currency_code = $value['Currency_Code'];
            $currency_name = $value['Currency_Description'];
            $decimal_rounding = $value['Decimal_Rounding'];
            $currency_ids_arr[] = $currency_code;
        }
        // print_r($currency_ids_arr);
        $currency_ids = implode(',', $currency_ids_arr);
        // BHD,GBP,EUR,INR,JOD,KWD,OMR,QAR,SAR,CHF,USD,AED
        // AED,BHD,EUR,GBP,INR,KWD,OMR,SAR,USD

        $path = 'currency/options/allow';
        $scope = 'default';
        $scope_id = '0';

        $query = $connection->select()->from('core_config_data', ['*'])->where('path = ?', $path);
        $result = $connection->fetchRow($query);
        print_r($result);
        if (empty($result)) {
            $sql = "insert into `core_config_data` (scope, scope_id, path, value) Values ('".$scope."', '".$scope_id."', '".$path."', '".$currency_ids."')";
            $connection->query($sql);
        } else {
            $sql = "UPDATE `core_config_data` SET `value` = '".$currency_ids."' WHERE `path` = '".$path."'";
            $connection->query($sql);
        }
        // flushCache
        $flushCache = $this->flushCache();
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
