<?php
namespace Flamingo\Badgefield\Setup;

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
            'web_badge',
            [
                'group' => 'custom-attribute',
                'label' => 'Web Badge',
                'type'  => 'text',
                'input' => 'multiselect',
                'source' => '',
                'required' => false,
                'sort_order' => 30,
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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