<?php
declare(strict_types=1);

namespace Amasty\Orderattr\Setup\Operation;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Amasty\Orderattr\Model\Entity\EntityData;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class ReplacePrimaryColumn
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $tableName = $setup->getTable(CreateEntityTable::TABLE_NAME);

        $setup->getConnection()->dropIndex(
            $tableName,
            $setup->getConnection()->getIndexName(
                $tableName,
                [CheckoutEntityInterface::ENTITY_ID],
                AdapterInterface::INDEX_TYPE_PRIMARY
            )
        );

        $setup->getConnection()->addColumn($tableName, EntityData::ROW_ID, [
            'type'      => Table::TYPE_INTEGER,
            'identity'  => true,
            'primary'   => true,
            'unsigned'  => true,
            'nullable'  => false,
            'comment'   => 'Row ID'
        ]);
    }
}
