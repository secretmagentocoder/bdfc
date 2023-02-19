<?php

namespace Amasty\Orderattr\Model\Entity;

use Amasty\Orderattr\Api\Data\EntityDataInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Indexer\StateInterface;

/**
 * @method \Amasty\Orderattr\Model\ResourceModel\Entity\Entity getResource()
 * @method \Amasty\Orderattr\Model\ResourceModel\Entity\Entity _getResource()
 * @method \Amasty\Orderattr\Model\ResourceModel\Entity\EntityData\Collection getCollection()
 */
class EntityData extends \Magento\Framework\Model\AbstractExtensibleModel implements EntityDataInterface
{
    const ROW_ID = 'row_id';

    protected $_eventPrefix = 'amasty_orderattr_entitydata';

    protected $_cacheTag = false;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init(\Amasty\Orderattr\Model\ResourceModel\Entity\Entity::class);
    }

    /**
     * Get a list of custom attribute codes.
     *
     * @return string[]
     */
    protected function getCustomAttributesCodes()
    {
        /** Magento < 2.2 fix
         * Avoid undefined index error in
         * \Magento\Eav\Model\Config::_initAttributes
         */
        try {
            return $this->getResource()->getAttributeCodes($this);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Verify custom attributes set on $data and unset if not a valid custom attribute
     *
     * @param array $data
     *
     * @return array processed data
     */
    protected function filterCustomAttributes($data)
    {
        if (empty($data[self::CUSTOM_ATTRIBUTES])) {
            return $data;
        }
        $customAttributesCodes = $this->getCustomAttributesCodes();
        $customAttributes = [];
        foreach ($data[self::CUSTOM_ATTRIBUTES] as $key => $value) {
            if ($value instanceof AttributeInterface) {
                if (in_array($value->getAttributeCode(), $customAttributesCodes)) {
                    $customAttributes[$value->getAttributeCode()] = $value;
                }
            } elseif (is_string($key) && in_array($key, $customAttributesCodes)) {
                $customAttributes[$key] = $this->customAttributeFactory->create()
                    ->setAttributeCode($key)
                    ->setValue($value);
            }
        }

        return [self::CUSTOM_ATTRIBUTES => $customAttributes];
    }

    /**
     * Revert magento fix MAGETWO-80426
     * Initialize customAttributes based on existing data
     */
    protected function initializeCustomAttributes()
    {
        if (!isset($this->_data[self::CUSTOM_ATTRIBUTES]) || $this->customAttributesChanged) {
            if (!empty($this->_data[self::CUSTOM_ATTRIBUTES])) {
                $customAttributes = $this->_data[self::CUSTOM_ATTRIBUTES];
            } else {
                $customAttributes = [];
            }
            $customAttributeCodes = $this->getCustomAttributesCodes();

            foreach ($customAttributeCodes as $customAttributeCode) {
                if (array_key_exists($customAttributeCode, $this->_data)) {
                    $customAttribute = $this->customAttributeFactory->create()
                        ->setAttributeCode($customAttributeCode)
                        ->setValue($this->_data[$customAttributeCode]);
                    $customAttributes[$customAttributeCode] = $customAttribute;
                    unset($this->_data[$customAttributeCode]);
                }
            }
            $this->_data[self::CUSTOM_ATTRIBUTES] = $customAttributes;
            $this->customAttributesChanged = false;
        }
    }

    /**
     * {@inheritdoc}
     * Recollect custom attributes if it was changed
     */
    public function getData($key = '', $index = null)
    {
        if ($key === '') {
            /** Represent model data and custom attributes as a flat array */
            $this->initializeCustomAttributes();
            $customAttributes = $this->_data[self::CUSTOM_ATTRIBUTES];
            $this->convertCustomAttributeValues($customAttributes);
            $data = array_merge($this->_data, $customAttributes);
            unset($data[self::CUSTOM_ATTRIBUTES]);
        } else {
            $data = parent::getData($key, $index);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * if custom attributes was not changed then change flag to avid customer attributes recollect
     */
    public function setData($key, $value = null)
    {
        $isChangedBefore = $this->customAttributesChanged;
        parent::setData($key, $value);
        if (!$isChangedBefore
            && is_string($key)
            && ($key != self::CUSTOM_ATTRIBUTES && !in_array($key, $this->getCustomAttributesCodes()))
        ) {
            $this->customAttributesChanged = false;
        }

        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->getParentEntityType() == self::ENTITY_TYPE_ORDER) {
            $this->reindex();
        }

        return parent::afterSave();
    }

    /**
     * Init indexing process after customer delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterDeleteCommit()
    {
        $this->reindex();

        return parent::afterDeleteCommit();
    }

    /**
     * Init indexing process after order save
     *
     * @return void
     */
    public function reindex()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerRegistry->get(\Amasty\Orderattr\Model\ResourceModel\Entity\Entity::GRID_INDEXER_ID);
        if (!$indexer->isScheduled() && !$indexer->isInvalid()) {
            $indexer->reindexRow($this->getId());
        }
    }

    /**
     * @inheritdoc
     */
    public function getParentId()
    {
        return $this->_getData(EntityDataInterface::PARENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setParentId($parentId)
    {
        $this->setData(EntityDataInterface::PARENT_ID, $parentId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParentEntityType()
    {
        return $this->_getData(EntityDataInterface::PARENT_ENTITY_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setParentEntityType($parentEntityType)
    {
        $this->setData(EntityDataInterface::PARENT_ENTITY_TYPE, $parentEntityType);

        return $this;
    }
}
