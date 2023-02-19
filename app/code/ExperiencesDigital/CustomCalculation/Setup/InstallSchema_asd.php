<?php

namespace ExperiencesDigital\CustomCalculation\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('custom_category_calculation');
        //Check for the existence of the table
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
               
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Category Code'
                )
                ->addColumn(
                    'parent_custom_category',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Parent Custom Category'
                )
                ->addColumn(
                    'active',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Is Active'
                )
                ->addColumn(
                    'location_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Location Type'
                )
                ->addColumn(
                   'limit_quantity',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Quantity Limit'
                )
                ->addColumn(
                    'limit_uom',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'UOM Limit'
                )
                ->addColumn(
                   'custom_calculation_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Custom Calculation Type'
                )
                ->addColumn(
                    'custom_charge_amount',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Custom Calculation Type'
                )
                ->addColumn(
                'parent_category',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Parent Category'
                )
                ->addColumn(
                'parent_category_limit_quanity',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Parent Category Qty Limit'
                )
                ->addColumn(
                'store_no',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Parent Category Qty Limit'
                )
                ->addColumn(
                'income_expense_account_no',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Income Expense Account NoGL_Account'
                ) ->addColumn(
                'gl_account',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'GL Account'
                ) 
            
                ->addColumn(
                    'starting_date',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Staring Date'
                )
                
                //Set comment for magetop_blog table
                ->setComment('Magetop Blog Table')
                //Set option for magetop_blog table
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
