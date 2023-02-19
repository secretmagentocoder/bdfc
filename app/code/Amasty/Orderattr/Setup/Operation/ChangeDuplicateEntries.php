<?php
declare(strict_types=1);

namespace Amasty\Orderattr\Setup\Operation;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class ChangeDuplicateEntries
{
    /**
     * @var string[]
     */
    private $tableKeys = [
        'datetime',
        'decimal',
        'int',
        'text',
        'varchar'
    ];

    /**
     * @var SchemaSetupInterface
     */
    private $setup;

    /**
     * @var string
     */
    private $entityTable;

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $this->setup = $setup;
        $this->entityTable = $setup->getTable(CreateEntityTable::TABLE_NAME);
        $this->deleteDuplicatesForQuote();
        $newEntityIds = $this->generateNewEntityIds();
        $this->changeIdsInTables($newEntityIds);
    }

    /**
     * Clear Duplicate Entities for Quotes
     */
    private function deleteDuplicatesForQuote(): void
    {
        $duplicateIds = $this->getDuplicateIdsByType(CheckoutEntityInterface::ENTITY_TYPE_QUOTE);
        $this->setup->getConnection()->delete(
            $this->entityTable,
            [
                CheckoutEntityInterface::ENTITY_ID . ' IN(?)' => $duplicateIds,
                CheckoutEntityInterface::PARENT_ENTITY_TYPE . ' =?' => CheckoutEntityInterface::ENTITY_TYPE_QUOTE
            ]
        );
    }

    /**
     * @return array array('old_id' => array('new_id', ...), ...)
     */
    private function generateNewEntityIds(): array
    {
        $duplicateIds = $this->getDuplicateIdsByType(CheckoutEntityInterface::ENTITY_TYPE_ORDER);
        $increment = 1;
        $newEntityIds = [];
        $lastEntityId = $this->getLastEntityId();
        foreach ($duplicateIds as $count => $duplicateId) {
            for ($i = 0; $i < $count; $i++) {
                $newEntityIds[$duplicateId][] = $lastEntityId + $increment;
                $increment++;
            }
        }

        return $newEntityIds;
    }

    /**
     * @param array $newEntityIds
     */
    private function changeIdsInTables(array $newEntityIds): void
    {
        foreach ($newEntityIds as $oldId => $newIds) {
            $this->changeIdsInEntityTable((int)$oldId, $newIds);
            $this->changeIdsInValueTables((int)$oldId, $newIds);
        }
    }

    /**
     * @param int $oldId
     * @param array $newIds
     */
    private function changeIdsInEntityTable(int $oldId, array $newIds): void
    {
        $duplicates = $this->getDuplicateEntitiesById($oldId);
        foreach ($duplicates as $entity) {
            $firstKey = array_key_first($newIds);
            $this->updateIdInEntityTable($entity, $newIds[$firstKey]);
            unset($newIds[$firstKey]);
        }
    }

    /**
     * @param int $entityId
     * @return array
     */
    private function getDuplicateEntitiesById(int $entityId): array
    {
        $select = $this->setup->getConnection()->select()
            ->from($this->entityTable)
            ->where(CheckoutEntityInterface::ENTITY_ID . ' =?', $entityId)
            ->where(CheckoutEntityInterface::PARENT_ENTITY_TYPE . ' =?', CheckoutEntityInterface::ENTITY_TYPE_ORDER)
            ->order('parent_id' . ' DESC');

        return $this->setup->getConnection()->fetchAll($select);
    }

    /**
     * @param array $entity
     * @param int $newId
     */
    private function updateIdInEntityTable(array $entity, int $newId): void
    {
        $this->setup->getConnection()->update(
            $this->entityTable,
            [CheckoutEntityInterface::ENTITY_ID => $newId],
            [
                CheckoutEntityInterface::ENTITY_ID . ' = ?' => $entity[CheckoutEntityInterface::ENTITY_ID],
                CheckoutEntityInterface::PARENT_ID . ' = ?' => $entity[CheckoutEntityInterface::PARENT_ID],
                CheckoutEntityInterface::PARENT_ENTITY_TYPE . ' = ?' => CheckoutEntityInterface::ENTITY_TYPE_ORDER
            ]
        );
    }

    /**
     * @param int $oldId
     * @param array $newIds
     */
    private function changeIdsInValueTables(int $oldId, array $newIds): void
    {
        foreach ($this->tableKeys as $tableKey) {
            $this->changeIdsInValueTable($tableKey, $oldId, $newIds);
        }
    }

    /**
     * @param string $tableKey
     * @param int $oldId
     * @param array $newIds
     */
    private function changeIdsInValueTable(string $tableKey, int $oldId, array $newIds): void
    {
        $entity = $this->getExistValueEntity($tableKey, $oldId);
        if (!$entity) {
            return;
        }

        $existEntityFlag = true;
        foreach ($newIds as $newId) {
            if ($existEntityFlag) {
                $this->updateIdInValueEntityTable($tableKey, $oldId, (int)$newId);
                $existEntityFlag = false;
            } else {
                unset($entity['value_id']);
                $entity['entity_id'] = $newId;
                $this->insertNewValueEntity($tableKey, $entity);

            }
        }
    }

    /**
     * @param string $tableKey
     * @param int $entityId
     * @return array|bool
     */
    private function getExistValueEntity(string $tableKey, int $entityId)
    {
        $select = $this->setup->getConnection()->select()
            ->from($this->setup->getTable(CreateEntityTable::TABLE_NAME . '_' . $tableKey))
            ->where(CheckoutEntityInterface::ENTITY_ID . ' =?', $entityId);

        return $this->setup->getConnection()->fetchRow($select);
    }

    /**
     * @param string $tableKey
     * @param int $oldId
     * @param int $newId
     */
    private function updateIdInValueEntityTable(string $tableKey, int $oldId, int $newId): void
    {
        $this->setup->getConnection()->update(
            $this->setup->getTable(CreateEntityTable::TABLE_NAME . '_' . $tableKey),
            ['entity_id' => $newId],
            ['entity_id = ?' => $oldId]
        );
    }

    /**
     * @param string $tableKey
     * @param array $valueEntity
     */
    private function insertNewValueEntity(string $tableKey, array $valueEntity): void
    {
        $this->setup->getConnection()->insertMultiple(
            $this->setup->getTable(CreateEntityTable::TABLE_NAME . '_' . $tableKey),
            $valueEntity
        );
    }

    /**
     * @return int
     */
    private function getLastEntityId(): int
    {
        $select = $this->setup->getConnection()->select()
            ->from($this->entityTable, [CheckoutEntityInterface::ENTITY_ID])
            ->order(CheckoutEntityInterface::ENTITY_ID . ' DESC')->limitPage(1, 1);

        return (int)$this->setup->getConnection()->fetchOne($select);
    }

    /**
     * @param int $type
     * @return array array('count_of_duplicates' => 'entity_id', ...)
     */
    private function getDuplicateIdsByType(int $type): array
    {
        $select = $this->setup->getConnection()->select()
            ->from($this->entityTable, ['COUNT(*)', CheckoutEntityInterface::ENTITY_ID])
            ->where(CheckoutEntityInterface::PARENT_ENTITY_TYPE . ' =?', $type)
            ->group([CheckoutEntityInterface::ENTITY_ID])
            ->having('COUNT(*) > 1');

        return $this->setup->getConnection()->fetchPairs($select);
    }
}
