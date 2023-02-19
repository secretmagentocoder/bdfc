<?php

namespace Amasty\Orderattr\Model\ResourceModel\Attribute;

use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;
use Amasty\Orderattr\Model\ResourceModel\Entity\Entity;
use Amasty\Orderattr\Setup\Operation\CreateEavAttributeCustomerGroupTable;
use Amasty\Orderattr\Setup\Operation\CreateEavAttributeStoreTable;
use Amasty\Orderattr\Setup\Operation\CreateShippingMethodsTable;

/**
 * @method \Amasty\Orderattr\Model\Attribute\Attribute[] getItems()
 */
class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
{
    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\Orderattr\Model\Attribute\Attribute::class,
            \Amasty\Orderattr\Model\ResourceModel\Attribute\Attribute::class
        );
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $entityType = $this->eavConfig->getEntityType(Entity::ENTITY_TYPE_CODE);
        $this->setEntityTypeFilter($entityType);
        return $this;
    }

    /**
     * Specify "is_filterable" filter
     *
     * @return $this
     */
    public function addGridFilter()
    {
        return $this->addFieldToFilter('additional_table.is_filterable', ['gt' => 0]);
    }

    /**
     * Specify "is_filterable" filter
     *
     * @return $this
     */
    public function addIsFilterableFilter()
    {
        return $this->addFieldToFilter('additional_table.' . CheckoutAttributeInterface::SHOW_ON_GRIDS, ['gt' => 0]);
    }

    /**
     * Add store filter
     *
     * @param int|array $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        if (!is_array($storeId)) {
            $storeId = [$storeId];
        }

        $this->join(
            CreateEavAttributeStoreTable::TABLE_NAME,
            CreateEavAttributeStoreTable::TABLE_NAME . '.' . CheckoutAttributeInterface::ATTRIBUTE_ID
                . '= main_table.' . CheckoutAttributeInterface::ATTRIBUTE_ID,
            null
        );

        $this->addFieldToFilter(CreateEavAttributeStoreTable::TABLE_NAME . '.store_id', ['in' => $storeId]);

        return $this;
    }

    /**
     * Add customer group filter
     *
     * @param int $groupId
     * @return $this
     */
    public function addCustomerGroupFilter($groupId)
    {
        $this->join(
            CreateEavAttributeCustomerGroupTable::TABLE_NAME,
            CreateEavAttributeCustomerGroupTable::TABLE_NAME . '.' . CheckoutAttributeInterface::ATTRIBUTE_ID
                . '= main_table.' . CheckoutAttributeInterface::ATTRIBUTE_ID,
            null
        );
        $this->addFieldToFilter(
            CreateEavAttributeCustomerGroupTable::TABLE_NAME . '.customer_group_id',
            (int) $groupId
        );
        return $this;
    }

    /**
     * Add shipping method filter
     *
     * @param string $shippingMethod
     * @return $this
     */
    public function addShippingMethodsFilter($shippingMethod)
    {
        $this->joinLeft(
            CreateShippingMethodsTable::TABLE_NAME,
            'main_table.' . CheckoutAttributeInterface::ATTRIBUTE_ID . '='
                . CreateShippingMethodsTable::TABLE_NAME . '.' . CheckoutAttributeInterface::ATTRIBUTE_ID,
            null
        )->addFieldToFilter(
            CreateShippingMethodsTable::TABLE_NAME . '.shipping_method',
            [
                ['null' => true],
                ['eq' => $shippingMethod]
            ]
        );

        return $this;
    }

    /**
     * Set order by attribute sort order
     *
     * @param string $direction
     * @return $this
     */
    public function setSortOrder($direction = self::SORT_ORDER_ASC)
    {
        return $this->setOrder(
            'additional_table.' . \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface::SORTING_ORDER,
            $direction
        );
    }
}
