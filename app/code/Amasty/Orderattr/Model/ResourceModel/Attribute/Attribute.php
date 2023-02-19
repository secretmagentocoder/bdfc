<?php

namespace Amasty\Orderattr\Model\ResourceModel\Attribute;

use Amasty\Orderattr\Model\Attribute\InputType\InputTypeProvider;
use Amasty\Orderattr\Setup\Operation\CreateEavAttributeCustomerGroupTable;
use Amasty\Orderattr\Setup\Operation\CreateEavAttributeStoreTable;
use Amasty\Orderattr\Setup\Operation\CreateAttributeTooltipTable;
use Amasty\Orderattr\Setup\Operation\CreateShippingMethodsTable;
use Magento\Eav\Model\ResourceModel\Entity\Type;
use Magento\Framework\Model\AbstractModel;
use Amasty\Orderattr\Model\Attribute\Attribute as EntityAttribute;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class Attribute extends \Magento\Eav\Model\ResourceModel\Entity\Attribute
{
    /**
     * Fields that should be serialized before persistence
     *
     * @var array
     */
    protected $_serializableFields = [EntityAttribute::VALIDATE_RULES => [[], []]];

    /**
     * @var InputTypeProvider
     */
    private $inputTypeProvider;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Type $eavEntityType,
        InputTypeProvider $inputTypeProvider,
        $connectionName = null
    ) {
        parent::__construct($context, $storeManager, $eavEntityType, $connectionName);
        $this->inputTypeProvider = $inputTypeProvider;
    }

    /**
     * Save store IDs Related to Attribute
     *
     * @param EntityAttribute|\Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _saveAvailableStores(AbstractModel $object)
    {
        $availableStores = $object->getData('store_ids');
        if (is_array($availableStores)) {
            $connection = $this->getConnection();
            if ($object->getId()) {
                $condition = ['attribute_id =?' => $object->getId()];
                $connection->delete($this->getTable(CreateEavAttributeStoreTable::TABLE_NAME), $condition);
            }
            foreach ($availableStores as $storeId) {
                if ($storeId == 0) {
                    continue;
                }
                $bind = ['attribute_id' => $object->getId(), 'store_id' => (int)$storeId];
                $connection->insert($this->getTable(CreateEavAttributeStoreTable::TABLE_NAME), $bind);
            }
        }

        return $this;
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    public function getAvailableInStoresByAttributeId($attributeId)
    {
        $connection = $this->getConnection();
        $bind = [':attribute_id' => $attributeId];
        $select = $connection->select()->from(
            $this->getTable(CreateEavAttributeStoreTable::TABLE_NAME),
            ['store_id']
        )->where(
            'attribute_id = :attribute_id'
        );

        return $connection->fetchCol($select, $bind);
    }

    /**
     * Save Customer Group IDs Related to Attribute
     *
     * @param EntityAttribute|\Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _saveCustomerGroups(AbstractModel $object)
    {
        $customerGroups = $object->getData('customer_groups');

        $connection = $this->getConnection();
        if ($object->getId()) {
            $condition = ['attribute_id =?' => $object->getId()];
            $connection->delete($this->getTable(CreateEavAttributeCustomerGroupTable::TABLE_NAME), $condition);
        }

        if (is_array($customerGroups)) {
            foreach ($customerGroups as $customerGroupId) {
                $bind = ['attribute_id' => $object->getId(), 'customer_group_id' => (int)$customerGroupId];
                $connection->insert($this->getTable(CreateEavAttributeCustomerGroupTable::TABLE_NAME), $bind);
            }
        }

        return $this;
    }

    /**
     * Save Attribute Shipping Methods
     *
     * @param EntityAttribute|\Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _saveShippingMethods(AbstractModel $object)
    {
        $shippingMethods = $object->getData('shipping_methods');

        $connection = $this->getConnection();
        if ($object->getId()) {
            $condition = ['attribute_id =?' => $object->getId()];
            $connection->delete($this->getTable(CreateShippingMethodsTable::TABLE_NAME), $condition);
        }

        if (is_array($shippingMethods)) {
            foreach ($shippingMethods as $shippingMethod) {
                $bind = [
                    'attribute_id' => $object->getId(),
                    'shipping_method' => $shippingMethod
                ];
                $connection->insert($this->getTable(CreateShippingMethodsTable::TABLE_NAME), $bind);
            }
        }

        return $this;
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    public function getCustomerGroupsByAttributeId($attributeId)
    {
        $connection = $this->getConnection();
        $bind = [':attribute_id' => $attributeId];
        $select = $connection->select()->from(
            $this->getTable(CreateEavAttributeCustomerGroupTable::TABLE_NAME),
            ['customer_group_id']
        )->where(
            'attribute_id = :attribute_id'
        );

        return $connection->fetchCol($select, $bind);
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    public function getShippingMethodsByAttributeId($attributeId)
    {
        $connection = $this->getConnection();
        $bind = [':attribute_id' => $attributeId];
        $select = $connection->select()->from(
            $this->getTable(CreateShippingMethodsTable::TABLE_NAME),
            ['shipping_method']
        )->where(
            'attribute_id = :attribute_id'
        );

        return $connection->fetchCol($select, $bind);
    }

    /**
     * Save Tooltip of attribute for stores
     *
     * @param EntityAttribute|\Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _saveTooltips(AbstractModel $object)
    {
        $tooltip = $object->getData('store_tooltips');
        $connection = $this->getConnection();
        if ($object->getId()) {
            $condition = ['attribute_id =?' => $object->getId()];
            $connection->delete($this->getTable(CreateAttributeTooltipTable::TABLE_NAME), $condition);
        }

        if (is_array($tooltip)) {
            foreach ($tooltip as $storeId => $tooltipText) {
                $bind = ['attribute_id' => $object->getId(), 'store_id' => $storeId, 'tooltip' => $tooltipText];
                $connection->insert($this->getTable(CreateAttributeTooltipTable::TABLE_NAME), $bind);
            }
        }

        return $this;
    }

    /**
     * @param int $attributeId
     *
     * @return array  [storeId => tooltipText]
     */
    public function getTooltipsByAttributeId($attributeId)
    {
        $connection = $this->getConnection();
        $bind = [':attribute_id' => $attributeId];
        $select = $connection->select()->from(
            $this->getTable(CreateAttributeTooltipTable::TABLE_NAME),
            ['store_id', 'tooltip']
        )->where(
            'attribute_id = :attribute_id'
        );

        return $connection->fetchPairs($select, $bind);
    }

    /**
     * Save additional attribute data after save attribute
     *
     * @param EntityAttribute|AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        //note: Additional attribute data is not inserted yet
        $this->_saveAvailableStores($object)
            ->_saveCustomerGroups($object)
            ->_saveTooltips($object)
            ->_saveShippingMethods($object);

        return parent::_afterSave($object);
    }

    /**
     * @param EntityAttribute|AbstractModel $object
     */
    protected function processAfterSaves(AbstractModel $object)
    {
        $this->serializeFields($object);
        parent::processAfterSaves($object);
        $this->unserializeFields($object);
    }

    /**
     * Restore origin data
     *
     * @param EntityAttribute|AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        $object->setOrigData();
        return $this;
    }

    /**
     * Update attribute default value
     *
     * @param EntityAttribute|AbstractModel $object
     * @param int|string $optionId
     * @param int $intOptionId
     * @param array $defaultValue
     * @return void
     */
    protected function _updateDefaultValue($object, $optionId, $intOptionId, &$defaultValue)
    {
        if (in_array($optionId, $object->getDefault())) {
            $inputType = $this->inputTypeProvider->getAttributeInputType($object->getFrontendInput());
            if (!$inputType || !$inputType->isManageOptions()) {
                return;
            }
            switch ($inputType->getOptionDefault()) {
                case 'checkbox':
                    $defaultValue[] = $intOptionId;
                    break;
                case 'radio':
                    $defaultValue = [$intOptionId];
                    break;
            }
        }
    }
}
