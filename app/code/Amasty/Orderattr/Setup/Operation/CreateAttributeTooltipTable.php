<?php

namespace Amasty\Orderattr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CreateAttributeTooltipTable
{
    const TABLE_NAME = 'amasty_order_attribute_tooltip';

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $attributeTable = $setup->getTable('eav_attribute');
        $storeTable = $setup->getTable('store');

        return $table = $setup->getConnection()
            ->newTable(
                $table
            )
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Attribute Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Store Id'
            )
            ->addColumn(
                'tooltip',
                Table::TYPE_TEXT,
                512,
                ['nullable' => false, 'default' => ''],
                'Tooltip'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [CheckoutAttributeInterface::ATTRIBUTE_ID, 'store_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [CheckoutAttributeInterface::ATTRIBUTE_ID, 'store_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    CheckoutAttributeInterface::ATTRIBUTE_ID,
                    $attributeTable,
                    CheckoutAttributeInterface::ATTRIBUTE_ID
                ),
                CheckoutAttributeInterface::ATTRIBUTE_ID,
                $attributeTable,
                CheckoutAttributeInterface::ATTRIBUTE_ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    'store_id',
                    $storeTable,
                    'store_id'
                ),
                'store_id',
                $storeTable,
                'store_id',
                Table::ACTION_CASCADE
            );

    }
}
