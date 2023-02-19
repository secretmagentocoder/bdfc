<?php

namespace Amasty\Orderattr\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpgradeTo300
     */
    private $upgradeTo300;

    /**
     * @var Operation\UpgradeTo310
     */
    private $upgradeTo310;

    /**
     * @var Operation\ChangeDuplicateEntries
     */
    private $changeDuplicateEntries;

    /**
     * @var Operation\AddEntityTableIndex
     */
    private $addEntityTableIndex;

    /**
     * @var Operation\ReplacePrimaryColumn
     */
    private $replacePrimaryColumn;

    public function __construct(
        Operation\UpgradeTo300 $upgradeTo300,
        Operation\UpgradeTo310 $upgradeTo310,
        Operation\ChangeDuplicateEntries $changeDuplicateEntries,
        Operation\AddEntityTableIndex $addEntityTableIndex,
        Operation\ReplacePrimaryColumn $replacePrimaryColumn
    ) {
        $this->upgradeTo300 = $upgradeTo300;
        $this->upgradeTo310 = $upgradeTo310;
        $this->changeDuplicateEntries = $changeDuplicateEntries;
        $this->addEntityTableIndex = $addEntityTableIndex;
        $this->replacePrimaryColumn = $replacePrimaryColumn;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '3.0.0', '<')) {
            $this->upgradeTo300->execute($setup);
        }

        if ($context->getVersion()
            && version_compare($context->getVersion(), '3.1.0', '<')
            && version_compare($context->getVersion(), '3.0.0', '>=')
        ) {
            $this->upgradeTo310->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '3.7.3', '<')) {
            $this->changeDuplicateEntries->execute($setup);
            $this->addEntityTableIndex->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '3.7.6', '<')) {
            $this->replacePrimaryColumn->execute($setup);
        }

        $setup->endSetup();
    }
}
