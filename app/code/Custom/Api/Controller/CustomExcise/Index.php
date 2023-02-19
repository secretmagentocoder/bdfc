<?php

namespace Custom\Api\Controller\CustomExcise;

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

        // WebRetailSetup
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $WebRetailSetup_url = $host.'/Company('.'\''.$company.'\''.')/WebRetailSetup?$format=application/json';
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $WebRetailSetup_url,
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

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        if (!empty($response_array['value'])) {
            $add_excise_calculation_base = '';
            foreach ($response_array['value'] as $key => $value) {
                $Local_Store_No = $value['Local_Store_No'];
                $Local_Store_Name = $value['Local_Store_Name'];
                $add_excise_calculation_base = $value['Add_Excise_Calculation_Base'];
            }
            print_r($add_excise_calculation_base);
        }

        // WebCompanyInformation
        $WebCompanyInformation_url = $host.'/Company('.'\''.$company.'\''.')/WebCompanyInformation?$format=application/json';
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $WebCompanyInformation_url,
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
        $company_info_array = json_decode($response_data,TRUE);
        print_r($company_info_array);

        $vat_for_excise_duty = 0;
        if (!empty($company_info_array['value'])) {
            $company_name = $company_info_array['value']['0']['Name'];
            echo $vat_for_excise_duty = $company_info_array['value']['0']['VATPercent_for_Excise_Duty'];
        }

        if (!empty($vat_for_excise_duty) && !empty($add_excise_calculation_base)) {
            // WebStore
            $WebStore_url = $host.'/Company('.'\''.$company.'\''.')/WebStore?$format=application/json';
            $header = array('Content-Type:application/atom+xml');
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $WebStore_url,
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
            $store_array = json_decode($response_data,TRUE);
            // print_r($store_array);

            if (!empty($store_array['value'])) {
                $store_excise_ids_arr = [];
                foreach ($store_array['value'] as $key => $value) {
                    $store_code = $value['No'];
                    $Web_Store = $value['Web_Store'];
                    $Excise_Applicable = $value['Excise_Applicable'];
                    if ($Excise_Applicable == true && $Web_Store == true) {
                        $store_excise_ids_arr[] = $store_code;
                    }
                }
                print_r($store_excise_ids_arr);

                // for add/update
                foreach ($store_excise_ids_arr as $key => $value) {
                    $store_code = $value;

                    // WebIncomeExpensesAc
                    $WebIncomeExpensesAc_url = $host.'/Company('.'\''.$company.'\''.')/WebIncomeExpensesAc?$format=application/json&$filter=Store_No%20eq%20%27'.$store_code.'%27';
                    $header = array('Content-Type:application/atom+xml');
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $WebIncomeExpensesAc_url,
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
                    $income_expense_array = json_decode($response_data,TRUE);
                    print_r($income_expense_array);

                    if (!empty($income_expense_array['value'])) {
                        $income_expense_ids_arr = [];
                        foreach ($income_expense_array['value'] as $key => $value) {
                            $Store_No = $value['Store_No'];
                            $income_expense_code = $value['No'];
                            $income_expense_description = $value['Description'];
                            $Custom_Duty_Account = $value['Custom_Duty_Account'];
                            $Excise_Account = $value['Excise_Account'];
                            if ($Excise_Account == true) {
                                $income_expense_ids_arr[] = $income_expense_code;

                                $store_id = '';
                                $tableName1 = $resource->getTableName('web_store_num');
                                $getlastid = "Select store_id FROM " . $tableName1 . " where store_num = '$Store_No'";
                                $result = $connection->fetchAll($getlastid);
                                $store_Id = $result[0]['store_id'];
                                if(in_array($store_Id, $store_ids)){
                                    $store_id = $store_Id;
                                }

                                $query = $connection->select()->from('web_excise_duty_setup', ['*'])->where('store_code = ?', $Store_No)->where('income_expense_code = ?', $income_expense_code);
                                $result = $connection->fetchRow($query);
                                print_r($result);

                                if (empty($result)) {
                                    $sql = "insert into `web_excise_duty_setup` (store_id, store_code, income_expense_code, income_expense_description, vat_for_excise_duty, add_excise_calculation_base) Values ('".$store_id."', '".$Store_No."', '".$income_expense_code."', '".$income_expense_description."', '".$vat_for_excise_duty."', '".$add_excise_calculation_base."')";
                                    $connection->query($sql);
                                }else{
                                    $sql = "UPDATE `web_excise_duty_setup` SET `income_expense_description` = '".$income_expense_description."', `vat_for_excise_duty` = '".$vat_for_excise_duty."', `add_excise_calculation_base` = '".$add_excise_calculation_base."' WHERE `store_code` = '".$Store_No."' AND `income_expense_code` = '".$income_expense_code."'";
                                    $connection->query($sql);
                                }

                            }
                        }
                        print_r($income_expense_ids_arr);
                    }

                }

                // for remove                
                $query = $connection->select()->from('web_excise_duty_setup', ['*']);
                $results = $connection->fetchAll($query);

                $all_magento_excise = [];
                foreach ($results as $value) {
                    $store_code = $value['store_code'];
                    $all_magento_excise []= $store_code;
                }
                // print_r($all_magento_excise);

                $all_remove_excise = array_diff($all_magento_excise, $store_excise_ids_arr);

                foreach ($all_remove_excise as $value) {
                    $store_code = $value;

                    $sql1 = "DELETE FROM `web_excise_duty_setup` WHERE `web_excise_duty_setup`.`store_code` = '".$store_code."'";
                    $connection->query($sql1);
                }

            }
        }

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
