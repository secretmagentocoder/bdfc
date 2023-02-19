<?php

namespace Custom\Api\Controller\Webstore;

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

class Index extends \Magento\Framework\App\Action\Action {

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
        )
    {
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
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/WebStore?$format=application/json';
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
        // $store_data = json_decode($response_data,TRUE);
        // print_r($store_data);
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // foreach ($store_data['value'] as $store_item) {
        //     if($store_item['Web_Store'] == true){
        //         $website_code = strtolower(str_replace("-","_", $store_item['Location_Code']));
        //         $store_number = $store_item['No'];
        //         $website_name = $store_item['Name'];
        //         /** @var \Magento\Store\Model\Website $website */
        //         $website = $this->websiteFactory->create();
        //         $website->load($website_code);
        //         if (!$website->getId()) {
        //             $website->setCode($website_code);
        //             $website->setName($website_name);
        //             $website->setSortOrder(4);
        //             // Set an existing store group id
        //             $website->setDefaultGroupId(2);
        //             try {
        //                 $website->save();
        //                 print_r("website created");
        //                 echo '<pre>';
        //             } catch (\Exception $e) {
        //                 $logger->info('fail to save website: ' . $website->getName() . ' ' . $e->getMessage());
        //                 if (!$website->getId()) {
        //                    //ignore if website actually did save
        //                     return;
        //                 }
        //             }
        //         } else {
        //             //do not make store/group if website did not save
        //             continue;
        //         }

        //         if ($website->getId()) {
        //             /** @var \Magento\Store\Model\Group $group */
        //             $group = $objectManager->create('Magento\Store\Model\Group');
        //             $group->setWebsiteId($website->getWebsiteId());
        //             $group->setName($website_name);
        //             $group->setCode($website_code);
        //             $group->setRootCategoryId(2);
        //             // $group->setDefaultStoreId($store->getId());
        //             try {
        //                 $group->save();
        //                 print_r("group created");
        //                 echo '<pre>';
        //             } catch (\Exception $e) {
        //                 $logger->info('fail to save group: ' . $group->getName() . ' ' . $e->getMessage());
        //                 return;
        //             }
        //         }
                
        //         /** @var \Magento\Store\Model\Store $store */
        //         $store = $this->storeFactory->create();
        //         $store->load($website_code);
        //         if(!$store->getId()){
        //             $store->setGroupId($group->getId());
        //             $store->setName($website_name);
        //             $store->setCode($website_code);
        //             // $store->setWebsiteId($website->getId());
        //             $store->setIsActive(true);
        //             try {
        //                 $store->save();
        //                 print_r("store created");
        //                 echo '<pre>';
        //             } catch (\Exception $e) {
        //                 echo $e->getMessage();
        //                 $logger->info('fail to save store: ' . $store->getName() . ' ' . $e->getMessage());
        //                 if (!$store->getId()) {
        //                     return;
        //                 }
        //             }
        //         }
        //         $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        //         $connection = $resource->getConnection();
        //         $tableName = $resource->getTableName('store_website_group'); //gives table name with prefix
    
        //         //Select Data from table
        //         // $sql = "Select * FROM " . $tableName;
        //         // $result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
        //         // echo "<pre>";print_r($result);
    
        //         //Insert Data into table
        //         $websiteId = $website->getId();
        //         $groupId = $group->getId();
        //         $storeId = $store->getId();
        //         $sql = "Insert Into " . $tableName . " (website_id, group_id, store_id, store_num) Values ($websiteId, $groupId, $storeId, $store_number)";
        //         $connection->query($sql);
        //     }
        // }
    }
}
