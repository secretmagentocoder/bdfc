<?php

namespace Amasty\Orderattr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;

class CreateEntityTable
{
    const TABLE_NAME = 'amasty_order_attribute_entity';

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

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Order Attribute entity table'
            )->addColumn(
                CheckoutEntityInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'default' => 0, 'unsigned' => true
                ],
                'Order Attribute Entity ID'
            )->addColumn(
                CheckoutEntityInterface::PARENT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Order Attribute Entity Parent ID'
            )->addColumn(
                CheckoutEntityInterface::PARENT_ENTITY_TYPE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Order Attribute Entity type'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    CheckoutEntityInterface::ENTITY_ID
                ),
                CheckoutEntityInterface::ENTITY_ID
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [CheckoutEntityInterface::PARENT_ID, CheckoutEntityInterface::PARENT_ENTITY_TYPE],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [CheckoutEntityInterface::PARENT_ID, CheckoutEntityInterface::PARENT_ENTITY_TYPE],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
    }
}
