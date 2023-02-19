<?php
declare(strict_types=1);

namespace Amasty\Orderattr\Setup\Operation;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddEntityTableIndex
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $tableName = $setup->getTable(CreateEntityTable::TABLE_NAME);
        $connection = $setup->getConnection();

        $connection->addIndex(
            $tableName,
            $connection->getIndexName(
                $tableName,
                [CheckoutEntityInterface::ENTITY_ID, CheckoutEntityInterface::PARENT_ENTITY_TYPE],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [CheckoutEntityInterface::ENTITY_ID, CheckoutEntityInterface::PARENT_ENTITY_TYPE],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
