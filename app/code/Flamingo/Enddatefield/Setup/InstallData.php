<?php
namespace Flamingo\Startdatefield\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

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
			\Magento\Catalog\Model\Category::ENTITY,
			'end_date',
			[
				'type'         => 'varchar',
				'label'        => 'End Date',
				'input'        => 'text',
				'sort_order'   => 100,
				'source'       => '',
				'required'     => false,
				'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				'visible'      => true,
				'user_defined' => true,
				'default'      => null,
				'group'        => '',
				'backend'      => ''
			]
		);
	}
}