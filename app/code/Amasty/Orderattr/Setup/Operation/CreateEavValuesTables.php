<?php

namespace Amasty\Orderattr\Setup\Operation;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateEavValuesTables
{
    const TABLE_NAME_PREFIX = 'amasty_order_attribute_entity_';

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->addIntColumn(
                $this->prepairTable($setup, self::TABLE_NAME_PREFIX . 'int')
                    ->setComment('Order Attribute Value Int')
            )
        );

        $setup->getConnection()->createTable(
            $this->addDecimalColumn(
                $this->prepairTable($setup, self::TABLE_NAME_PREFIX . 'decimal')
                    ->setComment('Order Attribute Value Decimal')
            )
        );

        $setup->getConnection()->createTable(
            $this->addDateTimeColumn(
                $this->prepairTable($setup, self::TABLE_NAME_PREFIX . 'datetime')
                    ->setComment('Order Attribute Value DateTime')
            )
        );

        $setup->getConnection()->createTable(
            $this->addTextColumn(
                $this->prepairTable($setup, self::TABLE_NAME_PREFIX . 'text')
                    ->setComment('Order Attribute Value Text')
            )
        );

        $setup->getConnection()->createTable(
            $this->addVarCharColumn(
                $this->prepairTable($setup, self::TABLE_NAME_PREFIX . 'varchar')
                    ->setComment('Order Attribute Value VarChar')
            )
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function prepairTable(SchemaSetupInterface $setup, $tableName)
    {
        $table = $setup->getTable($tableName);

        return $setup->getConnection()
            ->newTable(
                $table
            )->addColumn(
                'value_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ],
                'Value ID'
            )->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false, 'default'  => 0
                ],
                'Attribute ID'
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false, 'default'  => 0
                ],
                'Entity ID'
            )->addIndex(
                $setup->getIdxName($table, ['entity_id', 'attribute_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                ['entity_id', 'attribute_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    $tableName,
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $tableName,
                    'entity_id',
                    CreateEntityTable::TABLE_NAME,
                    CheckoutEntityInterface::ENTITY_ID
                ),
                'entity_id',
                $setup->getTable(CreateEntityTable::TABLE_NAME),
                CheckoutEntityInterface::ENTITY_ID,
                Table::ACTION_CASCADE
            );
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    private function addIntColumn(Table $table)
    {
        return $table->addColumn(
            'value',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'default' => null],
            'Value'
        );
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    private function addDecimalColumn(Table $table)
    {
        return $table->addColumn(
            'value',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => null],
            'Value'
        );
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    private function addDateTimeColumn(Table $table)
    {
        return $table->addColumn(
            'value',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null],
            'Value'
        );
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    private function addTextColumn(Table $table)
    {
        return $table->addColumn(
            'value',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'default' => ''],
            'Value'
        );
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    private function addVarCharColumn(Table $table)
    {
        return $table->addColumn(
            'value',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Value'
        );
    }
}
