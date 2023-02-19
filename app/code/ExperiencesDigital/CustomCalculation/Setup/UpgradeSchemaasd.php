<?php

namespace ExperiencesDigital\CustomCalculation\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('custom_category_calculation'),
                'parent_custom_category',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Parent Custom Category',
                   
                ],
                'parent_custom_category',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Parent Custom Category',

                ]
                
            );
        }



        $installer->endSetup();
    }
}
