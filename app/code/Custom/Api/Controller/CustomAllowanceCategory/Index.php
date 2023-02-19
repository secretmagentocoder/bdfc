<?php

namespace Custom\Api\Controller\CustomAllowanceCategory;

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

        $store_ids = array('2', '3', '4');
        $storeLists = ['1E', '2E', '8'];

        // WebCustomCategory
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $WebCustomCategory_url = $host.'/Company('.'\''.$company.'\''.')/WebCustomCategory?$format=application/json&$filter=Active%20eq%20true';
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $WebCustomCategory_url,
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
        $category_data = json_decode($response_data,TRUE);
        echo '<pre>';
        print_r($category_data);

        if (!empty($category_data['value'])) {
            // for add/update
            foreach ($category_data['value'] as $category_item) {
                $category_code = $category_item['Code'];
                $category_code_ = urlencode($category_code);
                $category_name = ucwords(strtolower($category_item['Description']));
                $parent_category_name = $category_item['Parent_Custom_Category'];
                $category_active = $category_item['Active'];

                // WebCustomAllowenceLimit
                $WebCustomAllowenceLimit_url = $host.'/Company('.'\''.$company.'\''.')/WebCustomAllowenceLimit?$format=application/json&$filter=Custom_Category_Code%20eq%20%27'.$category_code_.'%27%20and%20Location_Type%20eq%20%27Arrival%27';
                $header = array('Content-Type:application/atom+xml');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $WebCustomAllowenceLimit_url,
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
                $custom_limit_data = json_decode($response_data,TRUE);
                print_r($custom_limit_data);

                foreach ($custom_limit_data['value'] as $value) {
                    $location_type = $value['Location_Type'];
                    $starting_date = $value['Starting_Date'];
                    $limit_quantity = $value['Limit_Quantity'];
                    $limit_uom = $value['Limit_UOM'];
                    $custom_calculation_type = $value['Custom_Calculation_Type'];
                    $custom_charge_amount = $value['Custom_Charge_Amount'];
                    $parent_category = $value['Parent_Category'];
                    $parent_category_limit_quanity = $value['Parent_Category_Limit_Quanity'];

                    $store_id = '0';
                    if ($location_type == 'Arrival') {
                        $store_id = '2';
                    }else if ($location_type == 'Departure') {
                        $store_id = '3';
                    }else if ($location_type == 'Delivery') {
                        $store_id = '4';
                    }else{
                        $store_id = '0';
                    }

                    $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                    $result = $connection->fetchRow($query);
                    print_r($result);

                    if (empty($result)) {
                        $sql = "insert into `web_custom_allowence_category` (category_code, category_name, location_type, store_id, starting_date, limit_quantity, limit_uom, custom_calculation_type, custom_charge_amount, parent_category_name, parent_category_limit_quanity) Values ('".$category_code."', '".$category_name."', '".$location_type."', '".$store_id."', '".$starting_date."', '".$limit_quantity."', '".$limit_uom."', '".$custom_calculation_type."', '".$custom_charge_amount."', '".$parent_category_name."', '".$parent_category_limit_quanity."')";
                        $connection->query($sql);
                    }else{
                        $sql = "UPDATE `web_custom_allowence_category` SET `category_name` = '".$category_name."', `location_type` = '".$location_type."', `store_id` = '".$store_id."', `starting_date` = '".$starting_date."', `limit_quantity` = '".$limit_quantity."', `limit_uom` = '".$limit_uom."', `custom_calculation_type` = '".$custom_calculation_type."', `custom_charge_amount` = '".$custom_charge_amount."', `parent_category_name` = '".$parent_category_name."', `parent_category_limit_quanity` = '".$parent_category_limit_quanity."' WHERE `category_code` = '".$category_code."'";
                        $connection->query($sql);
                    }

                }

                // 
                $store_code = '2E';

                // WebStoreWiseCustomAcc
                $WebStoreWiseCustomAcc_url = $host.'/Company('.'\''.$company.'\''.')/WebStoreWiseCustomAcc?$format=application/json&$filter=Custom_Category_Code%20eq%20%27'.$category_code_.'%27%20and%20Store_No%20eq%20%27'.$store_code.'%27';
                $header = array('Content-Type:application/atom+xml');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $WebStoreWiseCustomAcc_url,
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
                $custom_account_data = json_decode($response_data,TRUE);
                print_r($custom_account_data);

                foreach ($custom_account_data['value'] as $value) {
                    $Custom_Category_Code = $value['Custom_Category_Code'];
                    $income_expense_account_no = $value['Income_Expense_Account_No'];
                    $custom_category_type = $value['Custom_Category_Type'];
                    $gl_account = $value['GL_Account'];

                    $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('category_code = ?', $category_code);
                    $result = $connection->fetchRow($query);

                    if (!empty($result)) {
                        $sql = "UPDATE `web_custom_allowence_category` SET `custom_category_type` = '".$custom_category_type."', `income_expense_account_no` = '".$income_expense_account_no."', `gl_account` = '".$gl_account."' WHERE `category_code` = '".$category_code."'";
                        $connection->query($sql);
                    }

                }
                
            }

            // for remove
            $all_api_category = [];
            foreach ($category_data['value'] as $category_item) {
                $Code = $category_item['Code'];
                $all_api_category []= $Code;
            }
            // print_r($all_api_category);
            
            $query = $connection->select()->from('web_custom_allowence_category', ['*']);
            $results = $connection->fetchAll($query);

            $all_magento_category = [];
            foreach ($results as $value) {
                $category_code = $value['category_code'];
                $all_magento_category []= $category_code;
            }
            // print_r($all_magento_category);

            $all_remove_category = array_diff($all_magento_category, $all_api_category);

            foreach ($all_remove_category as $value) {
                $category_code = $value;

                $sql1 = "DELETE FROM `web_custom_allowence_category` WHERE `web_custom_allowence_category`.`category_code` = '".$category_code."'";
                $connection->query($sql1);
            }
        }

        // WebCustomParentCategoryLimit
        $WebCustomParentCategoryLimit_url = $host.'/Company('.'\''.$company.'\''.')/WebCustomParentCategoryLimit?$format=application/json';
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $WebCustomParentCategoryLimit_url,
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
        $response_array = json_decode($response_data,TRUE);
        print_r($response_array);

        if (!empty($response_array['value'])) {
            foreach ($response_array['value'] as $key => $value) {
                $parent_category_code = $value['Category_Code'];
                $parent_location_type = $value['Location_Type'];
                $parent_starting_date = $value['Starting_Date'];
                $parent_limit_quantity = $value['Quantity_Limit'];
                $parent_category_code_ = urlencode($parent_category_code);

                $store_code = '0';
                if ($location_type == 'Arrival') {
                    $store_code = '2E';
                }else if ($location_type == 'Departure') {
                    $store_code = '1E';
                }else if ($location_type == 'Delivery') {
                    $store_code = '8';
                }

                // WebStoreWiseCustomAcc
                $WebStoreWiseCustomAcc_url = $host.'/Company('.'\''.$company.'\''.')/WebStoreWiseCustomAcc?$format=application/json&$filter=Custom_Category_Code%20eq%20%27'.$parent_category_code_.'%27%20and%20Store_No%20eq%20%27'.$store_code.'%27';
                $header = array('Content-Type:application/atom+xml');
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $WebStoreWiseCustomAcc_url,
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
                $custom_account_data = json_decode($response_data,TRUE);
                print_r($custom_account_data);

                foreach ($custom_account_data['value'] as $value) {
                    $Custom_Category_Code = $value['Custom_Category_Code'];
                    $income_expense_account_no = $value['Income_Expense_Account_No'];
                    $custom_category_type = $value['Custom_Category_Type'];
                    $gl_account = $value['GL_Account'];

                    $query = $connection->select()->from('web_custom_allowence_category', ['*'])->where('parent_category_name = ?', $Custom_Category_Code);
                    $results = $connection->fetchAll($query);
                    print_r($results);

                    if (!empty($results)) {
                        foreach ($results as $value) {
                            $entity_id = $value['id'];

                            $sql = "UPDATE `web_custom_allowence_category` SET `custom_category_type` = '".$custom_category_type."', `income_expense_account_no` = '".$income_expense_account_no."', `gl_account` = '".$gl_account."' WHERE `id` = '".$entity_id."'";
                            $connection->query($sql);
                        }
                    }

                }
            
            }
        }

        // reindexAll
        // $reindexAll = $this->reindexAll();

        // flushCache
        // $flushCache = $this->flushCache();
    }

    // reindexAll
    public function reindexAll() {
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
