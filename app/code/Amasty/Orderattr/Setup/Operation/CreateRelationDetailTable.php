<?php

namespace Amasty\Orderattr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Orderattr\Api\Data\RelationDetailInterface;

class CreateRelationDetailTable
{
    const TABLE_NAME = 'amasty_order_attribute_relation_details';

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
                'Amasty Order Attribute Relation Details Table'
            )->addColumn(
                RelationDetailInterface::RELATION_DETAIL_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ],
                'Order Attribute Relation Details ID'
            )->addColumn(
                RelationDetailInterface::ATTRIBUTE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'default' => 0, 'unsigned' => true
                ],
                'Order Attribute Relation Attribute ID'
            )->addColumn(
                RelationDetailInterface::OPTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'default' => 0, 'unsigned' => true
                ],
                'Order Attribute Option ID'
            )->addColumn(
                RelationDetailInterface::DEPENDENT_ATTRIBUTE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Order Attribute Dependent Attribute ID'
            )->addColumn(
                RelationDetailInterface::RELATION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Order Attribute Relation Details for Relation ID'
            );
    }
}
