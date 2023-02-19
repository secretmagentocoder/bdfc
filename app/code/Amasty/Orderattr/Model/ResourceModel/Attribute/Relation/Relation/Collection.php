<?php

namespace Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\Relation;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Orderattr\Model\Attribute\Relation\Relation::class,
            \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\Relation::class
        );
    }
}
