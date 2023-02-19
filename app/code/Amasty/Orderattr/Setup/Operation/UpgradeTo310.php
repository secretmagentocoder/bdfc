<?php

namespace Amasty\Orderattr\Setup\Operation;

use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo310
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $attributesTable = $setup->getTable(CreateEavAttributeTable::TABLE_NAME);
        $setup->getConnection()->addColumn(
            $attributesTable,
            CheckoutAttributeInterface::INCLUDE_IN_EMAIL,
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => true,
                'nullable' => true,
                'comment' => 'Include Attribute in Emails'
            ]
        );
    }
}
