<?php

namespace Ecommage\CustomerPersonalDetail\Model\ResourceModel\Country;

use Magento\Framework\Event\ManagerInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    public function __construct(\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, ManagerInterface $eventManager, \Magento\Framework\DB\Adapter\AdapterInterface $connection = null, \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        $this->_init('Ecommage\CustomerPersonalDetail\Model\Country', 'Ecommage\CustomerPersonalDetail\Model\ResourceModel\Country');
    }

}
