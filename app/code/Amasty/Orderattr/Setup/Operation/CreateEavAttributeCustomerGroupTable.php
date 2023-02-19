<?php

namespace Amasty\Orderattr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CreateEavAttributeCustomerGroupTable
{
    const TABLE_NAME = 'amasty_order_attribute_eav_attribute_customer_group';

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
        $customerGroupTable = $setup->getTable('customer_group');
        $describeTable = $setup->getConnection()->describeTable($customerGroupTable);
        if ($describeTable['customer_group_id']['DATA_TYPE'] == 'int') {
            $fieldType = Table::TYPE_INTEGER;
        } else {
            $fieldType = Table::TYPE_SMALLINT;
        }

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Order Attribute Customer Group Relation table'
            )->addColumn(
                CheckoutAttributeInterface::ATTRIBUTE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'default' => 0, 'unsigned' => true
                ],
                'Order Attribute Entity ID'
            )->addColumn(
                'customer_group_id',
                $fieldType,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Customer Group ID'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [CheckoutAttributeInterface::ATTRIBUTE_ID, 'customer_group_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [CheckoutAttributeInterface::ATTRIBUTE_ID, 'customer_group_id'],
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
                    'customer_group_id',
                    $customerGroupTable,
                    'customer_group_id'
                ),
                'customer_group_id',
                $customerGroupTable,
                'customer_group_id',
                Table::ACTION_CASCADE
            );
    }
}
