<?php

namespace Amasty\Orderattr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Orderattr\Api\Data\RelationInterface;

class CreateRelationTable
{
    const TABLE_NAME = 'amasty_order_attribute_relation';

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
                'Amasty Order Attribute Relation Table'
            )->addColumn(
                RelationInterface::RELATION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ],
                'Order Attribute Relation ID'
            )->addColumn(
                RelationInterface::NAME,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true, 'default' => null
                ],
                'Order Attribute Relation Name'
            );
    }
}
