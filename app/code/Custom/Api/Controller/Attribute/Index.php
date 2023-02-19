<?php

namespace Custom\Api\Controller\Attribute;

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
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        // $this->eavSetupFactory = $eavSetupFactory;
        $this->navConfigProvider = $navConfigProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $response = array();
        $host = $this->navConfigProvider->getHost();
        $company = $this->navConfigProvider->getCompany();
        $user = $this->navConfigProvider->getUser();
        $password = $this->navConfigProvider->getPassword();
        $attribute_url = $host.'/Company('.'\''.$company.'\''.')/WebAttributeList?$format=application/json';
        $header = array('Content-Type:application/atom+xml');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $attribute_url,
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
        $response_array = json_decode($response_data, TRUE);
        print_r($response_array);
        $all_attributes = [];
        foreach ($response_array['value'] as $key => $value) {
            $Attribute_ID = $value['Attribute_ID'];
            $Attribute_Name = $value['Attribute_Name'];
            $Attribute_Type = $value['Type'];
            $Attribute_Values = $value['Values'];
            $Attribute_Description = $value['Attribute_Description'];
            $from = [' ', '-', '&', '$'];
            $to = ['_', '_', '_', '_'];
            $Attribute_slug = str_replace($from, $to, strtolower($Attribute_Name));

            $fields = [];
            $fields['id'] = $Attribute_ID;
            $fields['type'] = $Attribute_Type;
            $fields['slug'] = $Attribute_slug;
            $fields['name'] = $Attribute_Name;
            $fields['description'] = $Attribute_Description;
            $fields['option'] = $Attribute_Values;
            $all_attributes[] = $fields;
        }
        print_r($all_attributes);

        $isCreated = false;
        $attributeResponse = false;
        foreach ($all_attributes as  $key => $value) {
            $attribute_type = $value['type'];
            $attribute_name = $value['name'];

            echo $isCreated = $this->isCreated($value);
            if ($isCreated == true) {
                // $isCreated true
                echo "Already exist";
                if ($attribute_type == 'Option') {
                    $attributeResponse = $this->updateAttributeSelect($value);
                    echo "Updated";
                }
            } else {
                // $isCreated false
                echo "Not exist";
                if ($attribute_type == 'Option') {
                    $attributeResponse = $this->createAttributeSelect($value);
                } elseif ($attribute_type == 'Boolean') {
                    $attributeResponse = $this->createAttributeBoolean($value);
                } elseif ($attribute_type == 'Text') {
                    $attributeResponse = $this->createAttributeText($value);
                } else {
                    $attributeResponse = $this->createAttributeText($value);
                }
                echo "Created";
            }
        }

        // reindexAll
        $reindexAll = $this->reindexAll();

        // flushCache
        $flushCache = $this->flushCache();
    }

    // isCreated
    public function isCreated($value)
    {

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $eavSetupFactory = $objectManager->get('Magento\Eav\Setup\EavSetupFactory');
        $setup = $objectManager->get('Magento\Framework\Setup\ModuleDataSetupInterface');

        // 
        $attribute_slug = $value['slug'];
        $attribute_name = $value['name'];

        // EavSetup $eavSetupFactory 
        $eavSetup = $eavSetupFactory->create(['setup' => $setup]);

        //
        if ($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $attribute_slug)) {
            // update name
            $current_attribute_id = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $attribute_slug);
            $attributeModel = $objectManager->create('Magento\Customer\Model\Attribute')->load($current_attribute_id);
            $attributeModel->setFrontendLabel($attribute_name)->save();

            return true;
        } else {
            return false;
        }
    }

    // updateAttributeSelect
    public function updateAttributeSelect($value)
    {

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $eavSetupFactory = $objectManager->get('Magento\Eav\Setup\EavSetupFactory');
        $setup = $objectManager->get('Magento\Framework\Setup\ModuleDataSetupInterface');

        // 
        $attribute_slug = $value['slug'];
        $attribute_option = $value['option'];
        $attribute_option_ = explode(',', $attribute_option);
        // print_r($attribute_option_);

        // EavSetup $eavSetupFactory 
        $eavSetup = $eavSetupFactory->create(['setup' => $setup]);

        // remove option
        $current_attribute_id = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $attribute_slug);
        $attributeModel = $objectManager->create('Magento\Customer\Model\Attribute')->load($current_attribute_id);
        $options = $attributeModel->getSource()->getAllOptions(); //get all options
        // print_r($options);

        $optionsToRemove = [];
        foreach ($options as $option) {
            if ($option['value'] && !in_array($option['label'], $attribute_option_)) {
                $optionsToRemove['delete'][$option['value']] = true;
                $optionsToRemove['value'][$option['value']] = true;
            }
        }
        $eavSetup->addAttributeOption($optionsToRemove);

        // add new option
        $new_options = ['attribute_id' => $current_attribute_id, 'values' => $attribute_option_];
        $eavSetup->addAttributeOption($new_options);
    }

    // createAttributeSelect
    public function createAttributeSelect($value)
    {

        // require __DIR__ . '/app/bootstrap.php';
        // $bootstrap = Bootstrap::create(BP, $_SERVER);
        // $obj = $bootstrap->getObjectManager();
        // $storeManager = $obj->get('\Magento\Store\Model\StoreManagerInterface');

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $eavSetupFactory = $objectManager->get('Magento\Eav\Setup\EavSetupFactory');
        $setup = $objectManager->get('Magento\Framework\Setup\ModuleDataSetupInterface');

        // 
        $attribute_type = $value['type'];
        $attribute_slug = $value['slug'];
        $attribute_name = $value['name'];
        $attribute_description = $value['description'];
        $attribute_option = $value['option'];
        $attribute_option_ = explode(',', $attribute_option);

        // EavSetup $eavSetupFactory 
        $eavSetup = $eavSetupFactory->create(['setup' => $setup]);

        //
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attribute_slug,
            [
                'group' => 'Custom Attribute',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => $attribute_name,
                'input' => 'select',
                'class' => '',
                'source' => '',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
                'option' => ['values' => $attribute_option_]
            ]
        );
    }

    // createAttributeBoolean
    public function createAttributeBoolean($value)
    {

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $eavSetupFactory = $objectManager->get('Magento\Eav\Setup\EavSetupFactory');
        $setup = $objectManager->get('Magento\Framework\Setup\ModuleDataSetupInterface');

        // 
        $attribute_type = $value['type'];
        $attribute_slug = $value['slug'];
        $attribute_name = $value['name'];
        $attribute_description = $value['description'];
        $attribute_option = $value['option'];
        $attribute_option_ = explode(',', $attribute_option);

        // EavSetup $eavSetupFactory 
        $eavSetup = $eavSetupFactory->create(['setup' => $setup]);

        //
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attribute_slug,
            [
                'group' => 'Custom Attribute',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => $attribute_name,
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

    // createAttributeText
    public function createAttributeText($value)
    {

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $eavSetupFactory = $objectManager->get('Magento\Eav\Setup\EavSetupFactory');
        $setup = $objectManager->get('Magento\Framework\Setup\ModuleDataSetupInterface');

        // 
        $attribute_type = $value['type'];
        $attribute_slug = $value['slug'];
        $attribute_name = $value['name'];
        $attribute_description = $value['description'];
        $attribute_option = $value['option'];
        $attribute_option_ = explode(',', $attribute_option);

        // EavSetup $eavSetupFactory 
        $eavSetup = $eavSetupFactory->create(['setup' => $setup]);

        //
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attribute_slug,
            [
                'group' => 'Custom Attribute',
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => $attribute_name,
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => null,
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
