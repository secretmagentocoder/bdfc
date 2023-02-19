<?php
/**
 * @package Ecommage_RaffleTickets
 */
declare(strict_types=1);

namespace Ecommage\RaffleTickets\Model\ResourceModel\RaffleTickets\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Ecommage\RaffleTickets\Model\ResourceModel\RaffleTickets\Collection as RaffleTicketsCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;

class Collection extends RaffleTicketsCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;
 
    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface        $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface       $eventManager
     * @param StoreManagerInterface  $storeManager
     * @param string                 $mainTable
     * @param string                 $eventPrefix
     * @param string                 $eventObject
     * @param string                 $resourceModel
     * @param string                 $model
     * @param AdapterInterface       $connection
     * @param AbstractDb             $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = Document::class,
        AdapterInterface  $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }
 
    /**
     * Function Get Aggregations
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }
 
    /**
     * Function Set Aggregations
     *
     * @param  AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param  SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param  int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param  \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
    /**
     * Function to join tables
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->columns('csi.country_name')
            ->joinLeft(
                ['csi' => $this->getTable('ecommage_nationalities')],
                'csi.country_id = main_table.nationality',
                []
            );
        $this->addFilterToMap('id', 'main_table.id');
    }
}
