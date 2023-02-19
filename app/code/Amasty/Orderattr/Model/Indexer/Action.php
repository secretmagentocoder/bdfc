<?php

namespace Amasty\Orderattr\Model\Indexer;

use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Amasty\Orderattr\Api\Data\EntityDataInterface;

class Action extends \Magento\Framework\Indexer\Action\Entity
{
    /**
     * Prepare select query
     *
     * @param array|int|null $ids
     * @return SourceProviderInterface
     */
    protected function prepareDataSource(array $ids = [])
    {
        $collection = $this->createResultCollection();
        if (!empty($ids)) {
            $collection->addFieldToFilter($this->getPrimaryResource()->getRowIdFieldName(), $ids);
        }

        return $collection;
    }
}
