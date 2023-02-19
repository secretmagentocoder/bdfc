<?php

namespace Custom\Api\Controller\Badges;

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
        ConfigProvider $navConfigProvider,
        array $data = array()
    )
    {
        $resultJsonFactory = $resultJsonFactory;
        $resourceConnection = $resourceConnection;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->recursivelyAPICall(); 
        // reindexAll
        $reindexAll = $this->reindexAll();
        // flushCache
        $flushCache = $this->flushCache();
	}
    
    public function recursivelyAPICall($offset = 0)
    {
        $response = array();
        $top = 10;
        $skip=$offset;
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company(%27'.$company.'%27)/WebBadges?$format=application/json&$skip='.$skip.'&$top='.$top;
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
        $response_array = json_decode($response_data,TRUE);
        if (count($response_array['value']) >= $top) {
            $newOffset = $offset + $top;
            $this->recursivelyAPICall($newOffset);
        }
        echo '<pre>';
        print_r($response_array);
        $all_badges = [];
        foreach ($response_array['value'] as $key => $value) {
            $Badge_ID = $value['Badge_ID'];
            $Badge_Name = $value['Badge_Name'];
            $Badge_Description = $value['Badge_Description'];
            $from = [' ', '-','&','$'];
            $to = ['_','_','_','_'];
            $badges_slug = str_replace($from, $to, strtolower($Badge_Name));
            $fields = [];
            $fields['id'] = $Badge_ID;
            $fields['slug'] = $badges_slug;
            $fields['name'] = $Badge_Name;
            $fields['description'] = $Badge_Description;
            $all_badges[] = $fields;
        }
        $isCreated = false;
        $attributeResponse = false;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName1 = $resource->getTableName('product_badges');
        foreach ($all_badges as  $key => $value) {
            $badge_id = $value['id'];
            $badge_slug = $value['slug'];
            $badge_name = $value['name'];
            $badge_description = $value['description'];
            echo $isCreated = $this->isCreated($value);
            if ($isCreated == true) {
                // $isCreated true
                echo "Already exist";
            }else{
                // $isCreated false
                echo "creating";
                print_r($badge_id);
                print_r($badge_name);
                print_r($badge_description);
                echo "<pre>";
                $attributeResponse = $this->createAttributeBoolean($value);
                $query = $connection->select()->from('product_badges', ['*'])->where('badge_id = ?', $badge_id);
                $result = $connection->fetchRow($query);
                if (empty($result)) {
                    $sql = "insert into " . $tableName1 . " (badge_id, badge_slug, badge_name, badge_description) Values ($badge_id, '".$badge_slug."', '".$badge_name."', '".$badge_description."')";
                    $connection->query($sql);
                    echo "Created";
                }
            }
        } 
    }

    // isCreated
    public function isCreated($value){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
        $eavSetupFactory = $objectManager->get('Magento\Eav\Setup\EavSetupFactory');
        $setup = $objectManager->get('Magento\Framework\Setup\ModuleDataSetupInterface');
        $badge_id = $value['id'];
        $badge_slug = $value['slug'];
        $badge_name = $value['name'];
        // EavSetup $eavSetupFactory 
        $eavSetup = $eavSetupFactory->create(['setup' => $setup]);
        if($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $badge_slug)) {
            // update name
            $current_attribute_id = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $badge_slug);
            $attributeModel = $objectManager->create('Magento\Customer\Model\Attribute')->load($current_attribute_id);
            $attributeModel->setFrontendLabel($badge_name)->save();
            return true;
        }else{
            return false;
        }
    }

    // createAttributeBoolean
    public function createAttributeBoolean($value){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
        $eavSetupFactory = $objectManager->get('Magento\Eav\Setup\EavSetupFactory');
        $setup = $objectManager->get('Magento\Framework\Setup\ModuleDataSetupInterface');
        $badge_id = $value['id'];
        $badge_slug = $value['slug'];
        $badge_name = $value['name'];
        $Badge_Description = $value['description'];
        // EavSetup $eavSetupFactory 
        $eavSetup = $eavSetupFactory->create(['setup' => $setup]);
        //
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $badge_slug,
            [
                'group' => 'Custom Attribute',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => $badge_name,
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
            ]
        );
    }   
    
    
    // reindexAll
    public function reindexAll()
    {
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
    public function flushCache()
    {
        // php bin/magento c:f

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $_cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
        $_cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
        $types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
        foreach ($types as $type) {
            $_cacheTypeList->cleanType($type);
        }
        foreach ($_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
