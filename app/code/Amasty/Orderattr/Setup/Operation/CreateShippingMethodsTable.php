<?php

namespace Amasty\Orderattr\Setup\Operation;

use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CreateShippingMethodsTable
{
    const TABLE_NAME = 'amasty_order_attribute_shipping_methods';

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
                'Amasty Order Attribute Shipping Methods'
            )->addColumn(
                CheckoutAttributeInterface::ATTRIBUTE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'default' => 0, 'unsigned' => true
                ],
                'Order Attribute EAV Attribute ID'
            )->addColumn(
                'shipping_method',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true, 'default' => null
                ],
                'Order Attribute Shipping Method Code'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [CheckoutAttributeInterface::ATTRIBUTE_ID, 'shipping_method'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [CheckoutAttributeInterface::ATTRIBUTE_ID, 'shipping_method'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    CheckoutAttributeInterface::ATTRIBUTE_ID,
                    'eav_attribute',
                    'attribute_id'
                ),
                CheckoutAttributeInterface::ATTRIBUTE_ID,
                $setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            );
    }
}
