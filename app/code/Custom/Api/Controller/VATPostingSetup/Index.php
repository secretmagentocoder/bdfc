<?php

namespace Custom\Api\Controller\VATPostingSetup;

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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company('.'\''.$company.'\''.')/WebStore?$format=application/json';
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

        $response_data = curl_exec($curl);
        curl_close($curl);
        echo '<pre>';
        $response_array = json_decode($response_data,TRUE);
        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        $store_vat_ids_arr = [];
        foreach ($response_array['value'] as $key => $value) {
            $store_id = $value['No'];
            $Store_VAT_Bus_Post_Gr = $value['Store_VAT_Bus_Post_Gr'];
            if(in_array($store_id, $storeLists)){
                // $store_vat_ids_arr[] = $Store_VAT_Bus_Post_Gr;
                $store_vat_ids_arr[$store_id] = $Store_VAT_Bus_Post_Gr;
            }
        }
        print_r($store_vat_ids_arr);

        $vat_api_url = $host.'/Company('.'\''.$company.'\''.')/WebVATPostingSetup?$format=application/json';
        $header = array('Content-Type:application/atom+xml');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $vat_api_url,
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

        $vat_class_arr = [];
        $vat_rate_code_arr = [];
        foreach ($response_array['value'] as $key => $value) {
            $VAT_Bus_Posting_Group = $value['VAT_Bus_Posting_Group'];
            $VAT_Prod_Posting_Group = $value['VAT_Prod_Posting_Group'];
            $VAT_Identifier = $value['VAT_Identifier'];
            $VAT_Percent = $value['VAT_Percent'];
            if(in_array($VAT_Bus_Posting_Group, $store_vat_ids_arr)){
                if (!empty($VAT_Prod_Posting_Group)) {
                    $vat_class = $VAT_Bus_Posting_Group.' - '.$VAT_Prod_Posting_Group;
                    $class_type = 'PRODUCT';

                    // for check tax class if exist
                    $select_query = $connection->select()->from('tax_class',['*'])->where('class_name = ?', $vat_class);
                    $results = $connection->fetchRow($select_query);
                    // print_r($results);
                    if(empty($results)){
                        $sql = "insert into `tax_class` (class_name, class_type) Values ('".$vat_class."', '".$class_type."')";
                        $connection->query($sql);
                    }

                    $tax_country_id = 'BH';
                    $tax_region_id = '0';
                    $tax_postcode = '*';
                    $rate_code = '*-*-*-'.$VAT_Bus_Posting_Group.' - '.$VAT_Prod_Posting_Group;
                    $rate = $VAT_Percent;

                    // for check tax rate if exist
                    $select_query = $connection->select()->from('tax_calculation_rate',['*'])->where('code = ?', $rate_code);
                    $results = $connection->fetchRow($select_query);
                    // print_r($results);
                    if(empty($results)){
                        $sql = "insert into `tax_calculation_rate` (tax_country_id, tax_region_id, tax_postcode, code, rate) Values ('".$tax_country_id."', '".$tax_region_id."', '".$tax_postcode."', '".$rate_code."', '".$rate."')";
                        $connection->query($sql);
                    } else {
                        $sql = "UPDATE `tax_calculation_rate` SET `rate` = '".$rate."', `tax_country_id` = '".$tax_country_id."' WHERE `code` = '".$rate_code."'";
                        $connection->query($sql);
                    }
                    $vat_class_arr[] = $vat_class;
                    $vat_rate_code_arr[] = $rate_code;

                    $rule_code = $vat_class.'__'.$rate_code;
                    // for check tax rule if exist
                    $select_query = $connection->select()->from('tax_calculation_rule',['*'])->where('code = ?', $rule_code);
                    $results = $connection->fetchRow($select_query);
                    // print_r($results);
                    if(empty($results)){
                        $sql = "insert into `tax_calculation_rule` (code) Values ('".$rule_code."')";
                        $connection->query($sql);
                    }

                    $tax_calculation_rate_id = '';
                    $tax_calculation_rule_id = '';
                    $product_tax_class_id = '';
                    $customer_tax_class_id = '3';
                    
                    // for get tax class id
                    $query = $connection->select()->from('tax_class',['*'])->where('class_name = ?', $vat_class);
                    $result = $connection->fetchRow($query);
                    if(!empty($result)){
                        $product_tax_class_id = $result['class_id'];
                    }
                    // for get tax rate id
                    $query = $connection->select()->from('tax_calculation_rate',['*'])->where('code = ?', $rate_code);
                    $result = $connection->fetchRow($query);
                    if(!empty($result)){
                        $tax_calculation_rate_id = $result['tax_calculation_rate_id'];
                    }
                    // for get tax rule id
                    $query = $connection->select()->from('tax_calculation_rule',['*'])->where('code = ?', $rule_code);
                    $result = $connection->fetchRow($query);
                    if(!empty($result)){
                        $tax_calculation_rule_id = $result['tax_calculation_rule_id'];
                    }

                    if (!empty($tax_calculation_rule_id)) {
                        $sql1 = "DELETE FROM `tax_calculation` WHERE `tax_calculation`.`tax_calculation_rule_id` = $tax_calculation_rule_id";
                        $connection->query($sql1);

                        $sql = "insert into `tax_calculation` (tax_calculation_rate_id, tax_calculation_rule_id, customer_tax_class_id, product_tax_class_id) Values ('".$tax_calculation_rate_id."', '".$tax_calculation_rule_id."', '".$customer_tax_class_id."', '".$product_tax_class_id."')";
                        $connection->query($sql);
                    }
                }
            }
        }
        print_r($vat_class_arr);
        print_r($vat_rate_code_arr);

        // flushCache
        // $flushCache = $this->flushCache();
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
