<?php
namespace Custom\Api\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory)
	{
		$this->eavSetupFactory = $eavSetupFactory;
	}

	public function install(
		ModuleDataSetupInterface $setup,
		ModuleContextInterface $context
	)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
            'custom_allowence_category',
            [
                'group' => 'custom-attribute',
                'label' => 'Custom Allowence Category',
                'type'  => 'text',
                'input' => 'select',
                'source' => 'Custom\Api\Model\Source\Customcategorydropdown',
                'required' => false,
                'sort_order' => 30,
                'backend' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE, // SCOPE_GLOBAL
                'visible' => true,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'wysiwyg_enabled' => true,
                'unique' => false,
                'apply_to' => ''
            ]
		);
	}
}