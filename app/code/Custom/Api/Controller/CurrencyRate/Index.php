<?php

namespace Custom\Api\Controller\CurrencyRate;

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

    private function getLastUpdatedCurrencyDate()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $currency_rate_url = $host.'/Company('.'\''.$company.'\''.')/WebCurrencyExchangeRate?$orderby=Starting_Date%20desc&$format=application/json';
        echo $currency_rate_url;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $currency_rate_url,
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $user.':'.$password,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response_data = curl_exec($curl);
        // var_dump($response_data);
        curl_close($curl);
        // echo "rr";
        // echo '<pre>';
        $response_array = json_decode($response_data, TRUE);
        $i=0;
        foreach ($response_array['value'] as $key => $value) {
            if($i==0){
                return $value['Starting_Date'];
                break;
            }
        }
    }

    public function execute()
    {
        // $lastUpdatedDay= $this->getLastUpdatedCurrencyDate();
        $today = date('Y-m-d');
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $currency_rate_url = $host.'/Company('.'\''.$company.'\''.')/WebCurrencyExchangeRate?$orderby=Starting_Date%20desc&$format=application/json';
        //echo $currency_rate_url;
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $currency_rate_url,
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $user.':'.$password,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_VERBOSE=>true,
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
        echo "<pre>";
        $response_array = json_decode($response_data,TRUE);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');

        $tableName = $resource->getTableName('directory_currency_rate');
        $default_currency = 'BHD';
        $updated_currencies_today=[];

        foreach ($response_array['value'] as $key => $value) {
            if(isset($updated_currencies_today) && in_array($value['Currency_Code'],$updated_currencies_today)){
                echo "Continuing".$value['Currency_Code']."<br>";
                continue;
            }else{
                array_push($updated_currencies_today,$value['Currency_Code']);
                echo "updating ".$value['Currency_Code'] . "<br>";
                $currency_code = $value['Currency_Code'];
                $currency_name = $value['Currency_Code'];
                $exchange_rate = 1;
                if ($value['POS_Rel_Exch_Rate_Amount'] > 0) {
                    $exchange_rate = 1 / $value['POS_Rel_Exch_Rate_Amount'];
                }
                $query = $connection->select()->from('directory_currency_rate', ['*'])->where('currency_from = ?', $default_currency)->where('currency_to = ?', $currency_code);
                $result = $connection->fetchRow($query);
                if (empty($result)) {
                    $sql = "insert into " . $tableName . " (currency_from, currency_to, rate) Values ('" . $default_currency . "', '" . $currency_code . "', '" . $exchange_rate . "')";
                    $connection->query($sql);
                } else {
                    $sql = "UPDATE " . $tableName . " SET `rate` = '" . $exchange_rate . "' WHERE `currency_from` = '" . $default_currency . "' AND `currency_to` = '" . $currency_code . "'";
                    $connection->query($sql);
                }
                $last_currency = $value['Currency_Code'];
            }
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
