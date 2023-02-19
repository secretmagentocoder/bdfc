<?php

namespace Amasty\Orderattr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;

class CreateEavAttributeTable
{
    const TABLE_NAME = 'amasty_order_attribute_eav_attribute';

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
                'Amasty Order Attribute EAV extension table'
            )->addColumn(
                CheckoutAttributeInterface::ATTRIBUTE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'default' => 0, 'unsigned' => true
                ],
                'Order Attribute EAV Attribute ID'
            )->addColumn(
                CheckoutAttributeInterface::IS_VISIBLE_ON_FRONT,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => false
                ],
                'Frontend visibility'
            )->addColumn(
                CheckoutAttributeInterface::IS_VISIBLE_ON_BACK,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => false
                ],
                'Backend visibility'
            )->addColumn(
                CheckoutAttributeInterface::MULTISELECT_SIZE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => true, 'default' => 0
                ],
                'Multiselect size'
            )->addColumn(
                CheckoutAttributeInterface::SORTING_ORDER,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true, 'default' => 0
                ],
                'Sorting order'
            )->addColumn(
                CheckoutAttributeInterface::CHECKOUT_STEP,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => true, 'default' => 0
                ],
                'Checkout Step'
            )->addColumn(
                CheckoutAttributeInterface::SHOW_ON_GRIDS,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => false
                ],
                'Show on Admin grids'
            )->addColumn(
                CheckoutAttributeInterface::INCLUDE_IN_PDF,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => false
                ],
                'Include to PDF documents'
            )->addColumn(
                CheckoutAttributeInterface::INCLUDE_IN_HTML_PRINT_ORDER,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => false
                ],
                'Include to HTML print order'
            )->addColumn(
                CheckoutAttributeInterface::SAVE_TO_FUTURE_CHECKOUT,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => false
                ],
                'Save Attribute to future checkout'
            )->addColumn(
                CheckoutAttributeInterface::APPLY_DEFAULT_VALUE,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => false
                ],
                'Apply Default value to Attribute'
            )->addColumn(
                CheckoutAttributeInterface::INCLUDE_IN_EMAIL,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => true
                ],
                'Include Attribute in Emails'
            )->addColumn(
                CheckoutAttributeInterface::REQUIRED_ON_FRONT_ONLY,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => false
                ],
                'Is attribute required'
            )->addColumn(
                CheckoutAttributeInterface::VALIDATE_RULES,
                Table::TYPE_TEXT,
                '64k',
                [],
                'Validate Rules'
            )->addColumn(
                CheckoutAttributeInterface::INPUT_FILTER,
                Table::TYPE_TEXT,
                255,
                [],
                'Input Filter'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    CheckoutAttributeInterface::ATTRIBUTE_ID
                ),
                CheckoutAttributeInterface::ATTRIBUTE_ID
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
