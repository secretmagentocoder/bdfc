<?php

namespace Amasty\Orderattr\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Operation\UpgradeDataTo300
     */
    private $upgradeDataTo300;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        Operation\UpgradeDataTo300 $upgradeDataTo300,
        \Magento\Eav\Model\Config $config
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->upgradeDataTo300 = $upgradeDataTo300;
        $this->config = $config;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (!$context->getVersion() || version_compare($context->getVersion(), '3.0.0', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create();
            $eavSetup->addEntityType(
                \Amasty\Orderattr\Model\ResourceModel\Entity\Entity::ENTITY_TYPE_CODE,
                [
                    'entity_model' => \Amasty\Orderattr\Model\ResourceModel\Entity\Entity::class,
                    'attribute_model' => \Amasty\Orderattr\Model\Attribute\Attribute::class,
                    'table' => Operation\CreateEntityTable::TABLE_NAME,
                    'entity_attribute_collection' => \Amasty\Orderattr\Model\ResourceModel\Attribute\Collection::class,
                    'additional_attribute_table' => Operation\CreateEavAttributeTable::TABLE_NAME
                ]
            );
            $this->config->clear();

            if ($context->getVersion() && version_compare($context->getVersion(), '3.0.0', '<')) {
                $this->upgradeDataTo300->execute($setup, $context);
            }
        }
        $setup->endSetup();
    }
}
