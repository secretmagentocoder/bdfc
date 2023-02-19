<?php

namespace Amasty\Orderattr\Model\Indexer;

use Magento\Framework\Indexer\AbstractProcessor;
use Amasty\Orderattr\Model\ResourceModel\Entity\Entity;

class ActionProcessor extends AbstractProcessor
{
    const INDEXER_ID = Entity::GRID_INDEXER_ID;
}
