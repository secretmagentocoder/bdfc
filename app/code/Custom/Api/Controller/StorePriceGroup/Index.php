<?php

namespace Custom\Api\Controller\StorePriceGroup;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\StoreFactory;
use Ecommage\NavSyncPromotion\Model\ConfigProvider;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
    protected $resourceConnection;
    protected $eavSetupFactory;
    protected $storeManager;
    protected $websiteFactory;
    protected $storeFactory;
    protected $categoryFactory;
    private $navConfigProvider;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection,
        WebsiteFactory $websiteFactory,
        StoreFactory $storeFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConfigProvider $navConfigProvider,
        array $data = array()
    ) {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->websiteFactory = $websiteFactory;
        $this->storeFactory = $storeFactory;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->recursivelyAPICall();
    }

    public function recursivelyAPICall($offset = 0)
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $top = 10;
        $skip = $offset;
        $store_url = $host.'/Company(%27'.$company.'%27)/WebStorePriceGroup?$format=application/json&$skip=' . $skip . '&$top=' . $top;
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
        echo '<pre>';
        $response_array = json_decode($response_data, TRUE);

        if (count($response_array['value']) >= $top) {
            $newOffset = $offset + $top;
            $this->recursivelyAPICall($newOffset);
        }
        $store_ids = array('1E', '2E', '8');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $tableName = $resource->getTableName('web_store_price_group');
        $default_currency = 'BHD';
        foreach ($response_array['value'] as $key => $value) {

            // $return[$value['Store']]= $value['Price_Group_Code'];
            $Store = $value['Store'];
            $Price_Group_Code = $value['Price_Group_Code'];
            $Price_Group_Description = $value['Price_Group_Description'];
            $Priority = $value['Priority'];
            if (in_array($Store, $store_ids)) {
                $magento_store_id = '';
                if ($Store == '1E') {
                    $magento_store_id = '3';
                } elseif ($Store == '2E') {
                    $magento_store_id = '2';
                } elseif ($Store == '8') {
                    $magento_store_id = '4';
                } else {
                    $magento_store_id = '';
                }
                $query = $connection->select()->from('web_store_price_group', ['*'])->where('price_group_code = ?', $Price_Group_Code)->where('store_id = ?', $Store);
                $result = $connection->fetchRow($query);
                print_r($result);
                if (empty($result)) {
                    $sql = "insert into " . $tableName . " (store_id, price_group_code, price_group_description, priority, magento_store_id) Values ('" . $Store . "', '" . $Price_Group_Code . "', '" . $Price_Group_Description . "', '" . $Priority . "', '" . $magento_store_id . "')";
                    $connection->query($sql);
                } else {
                    $sql = "UPDATE " . $tableName . " SET `priority` = '" . $Priority . "' WHERE `price_group_code` = '" . $Price_Group_Code . "' AND `store_id` = '" . $Store . "'";
                    $connection->query($sql);
                }
            }
        }
    }
}
