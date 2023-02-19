<?php

namespace Amasty\Orderattr\Model\ResourceModel\Attribute\Relation;

use Amasty\Orderattr\Api\Data\RelationDetailInterface;
use Amasty\Orderattr\Setup\Operation\CreateRelationDetailTable;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RelationDetails extends AbstractDb
{
    public function _construct()
    {
        $this->_init(
            CreateRelationDetailTable::TABLE_NAME,
            RelationDetailInterface::RELATION_DETAIL_ID
        );
    }

    /**
     * Delete Details data for relation
     *
     * @param int $relationId
     */
    public function deleteAllDetailForRelation($relationId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['relation_id = ?' => $relationId]);
    }

    public function fastDelete($ids)
    {
        $db = $this->getConnection();
        $table = $this->getTable('amasty_order_attribute_relation_details');
        $db->delete($table, $db->quoteInto(RelationDetailInterface::RELATION_DETAIL_ID . ' IN(?)', $ids));
    }
}
