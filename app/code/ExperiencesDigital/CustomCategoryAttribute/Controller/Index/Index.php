<?php

namespace ExperiencesDigital\CustomCategoryAttribute\Controller\Index;

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

    protected $objectManager;

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
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resourceConnection =$this->objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->updateCategoryCustomAttribute();
    }

    //recursively category api call
    private function updateCategoryCustomAttribute()
    {

        echo "<pre>";

        $categoryData = $this->curlCall();
        $this->processCategoryData($categoryData);
        echo "Done processing";
    }


    private function processCategoryData($categoryData)
    {
        if (is_array($categoryData) && count($categoryData) > 0) {
            foreach ($categoryData as $category) {
                
               
               
                $this->updateCategoryData($category);
                
            }
        }
    }

    private function updateCategoryData($category)
    {
        $select_query = $this->resourceConnection->select()->from('catalog_category_entity', ['*'])->where('web_category_code = ?', $category['Web_Category_Code']);
        $category_data = $this->resourceConnection->fetchRow($select_query);
        

        if (!empty($category_data)) {
            //Add image to productt
            $categoryID = $category_data['entity_id'];

            // Post image to magento 
            $baseUrl = $this->navConfigProvider->getBaseUrl();
            $ch = curl_init($baseUrl."index.php/rest/all/V1/categories/" . $categoryID);
            # Setup request to send json via POST.
            $age_limit= $category['Age_Limit'];
            $categoryIcon= !empty($category['Category_Image_Pathway'])? $category['Category_Image_Pathway']:''; 
            if(!empty($categoryIcon)){
                $warning_message = "<img src='$categoryIcon' width='50px' height='auto'><p>" . $category['Warning_Message']."</p>";
            }else{
                $warning_message = $category['Warning_Message'];
            }
    
            $catDb=$this->categoryFactory->create()->setStoreId(0)->load($categoryID);



            $catDb->setAgeLimit($category['Age_Limit']);
            $catDb->setWarningMessage($warning_message);
            $catDb->setWebQtyLimit($category['Web_Qty_Limit']);
            $catDb->save();
          

        }
    }


    private function curlCall()
    {
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $store_url = $host.'/Company(%27'.$company.'%27)/WebCategory?$format=application/json';
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
}
