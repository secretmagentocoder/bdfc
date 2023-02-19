<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\ResourceModel\CollectionUpdater;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterAbstract;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use MageWorx\OptionBase\Model\Product\Option\CollectionUpdaters as OptionCollectionUpdaters;
use MageWorx\OptionBase\Model\Product\Option\Value\CollectionUpdaters as ValueCollectionUpdaters;
use Magento\Framework\App\State;
use MageWorx\OptionBase\Helper\Data;
use MageWorx\OptionBase\Helper\CustomerVisibility;

class Value extends CollectionUpdaterAbstract
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var CustomerVisibility
     */
    protected $helperCustomerVisibility;

    /**
     * @var bool
     */
    protected $isVisibilityFilterRequired;

    /**
     * @param ResourceConnection $resource
     * @param Collection $collection
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     * @param OptionCollectionUpdaters $optionCollectionUpdaters
     * @param ValueCollectionUpdaters $valueCollectionUpdaters
     * @param CustomerVisibility $helperCustomerVisibility
     * @param State $state
     * @param Data $helperData
     * @param array $conditions
     */
    public function __construct(
        ResourceConnection $resource,
        Collection $collection,
        CollectionUpdaterRegistry $collectionUpdaterRegistry,
        OptionCollectionUpdaters $optionCollectionUpdaters,
        ValueCollectionUpdaters $valueCollectionUpdaters,
        CustomerVisibility $helperCustomerVisibility,
        State $state,
        Data $helperData,
        $conditions = []
    ) {
        parent::__construct(
            $collection,
            $resource,
            $collectionUpdaterRegistry,
            $optionCollectionUpdaters,
            $valueCollectionUpdaters,
            $helperData,
            $conditions
        );

        $this->connection                 = $resource->getConnection();
        $this->state                      = $state;
        $this->helperCustomerVisibility   = $helperCustomerVisibility;
        $this->isVisibilityFilterRequired = $this->helperCustomerVisibility->isVisibilityFilterRequired();
    }

    /**
     * Process option value collection by updaters
     */
    public function process()
    {
        $partFrom = $this->collection->getSelect()->getPart('from');

        if ($this->isVisibilityFilterRequired && $this->helperData->isEnabledIsDisabled()) {
            $this->collection->addFieldToFilter('main_table.disabled', '0');
        }

        foreach ($this->valueCollectionUpdaters->getData() as $valueCollectionUpdatersItem) {
            $alias = $valueCollectionUpdatersItem->getTableAlias();
            if (array_key_exists($alias, $partFrom)) {
                continue;
            }

            $this->collection->getSelect()->joinLeft(
                $valueCollectionUpdatersItem->getFromConditions($this->conditions),
                $valueCollectionUpdatersItem->getOnConditionsAsString(),
                $valueCollectionUpdatersItem->getColumns()
            );
        }
    }
}
