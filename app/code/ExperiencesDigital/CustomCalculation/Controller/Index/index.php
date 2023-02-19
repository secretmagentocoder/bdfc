<?php

namespace ExperiencesDigital\CustomCalculation\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Store\Model\StoreFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    protected $eavSetupFactory;

    protected $storeFactory;

    private $navConfigProvider;

    protected $customCatTableName = "custom_category_calculation";

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
        $categoryData = $this->curlCall();
        $this->processCategoryData($categoryData);
    }




    private function processCategoryData($categoryData)
    {

        if (is_array($categoryData) && count($categoryData) > 0) {
            foreach ($categoryData as $category) {
                $catData = [];
                // print_r($category);
                $catAllowanceLimit = $this->curlCallForCustomCategoryAllowanceLimits($category['Code']);
                echo "<pre>";
                // print_r($catAllowanceLimit);
                $array1 = array_merge($catData, $catAllowanceLimit);
                $catData = array_merge($array1, $category);
                $this->insertCustomCategoryAllowance($catData);
                print_r($catData);
                //$this->updateCategoryData($category);
                array_merge($catData, $catAllowanceLimit, $category);
                flush();
                ob_flush();
            }
        }
    }

    protected function insertCustomCategoryAllowance($catDta)
    {

        if ($this->resourceConnection->isTableExists($this->customCatTableName) == true) {
            unset($catDta['ETag']);
            $catCode = $catDta['Code'];
            $this->resourceConnection->delete($this->customCatTableName, ["Code = '$catCode'"]);
            $this->resourceConnection->insert($this->customCatTableName, $catDta);
        }
    }



    private function curlCall()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/WebCustomCategory?$format=application/json';
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

    private function curlCallForCustomCategoryAllowanceLimits($category)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $category = urlencode($category);
        $category = "'" . $category . "'";
        $store_url = $host.'/Company(%27'.$company.'%27)/WebCustomAllowenceLimit?$format=application/json&$filter=Custom_Category_Code%20eq%20' . $category;
        // echo $store_url;
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
            return $response['value'][0];
        } else {
            return '';
        }
    }
}
