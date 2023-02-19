<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model;

use MageWorx\OptionBase\Helper\Data as Helper;
use MageWorx\OptionBase\Model\Product\Attributes as ProductAttributes;
use MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use MageWorx\OptionBase\Model\Product\Option\Value\Attributes as OptionValueAttributes;
use Magento\Framework\App\ResourceConnection;

class AttributeSaver
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ProductAttributes
     */
    protected $productAttributes;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * @var OptionValueAttributes
     */
    protected $optionValueAttributes;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Array of attribute data for multiple insert
     *
     * @var array
     */
    protected $attributeData = [];

    /**
     * @var array
     */
    protected $newGroupOptionIds = [];

    /**
     * @param Helper $helper
     * @param ProductAttributes $productAttributes
     * @param OptionAttributes $optionAttributes
     * @param ResourceConnection $resource
     * @param OptionValueAttributes $optionValueAttributes
     */
    public function __construct(
        ProductAttributes $productAttributes,
        OptionAttributes $optionAttributes,
        OptionValueAttributes $optionValueAttributes,
        ResourceConnection $resource,
        Helper $helper
    ) {
        $this->productAttributes     = $productAttributes;
        $this->optionAttributes      = $optionAttributes;
        $this->optionValueAttributes = $optionValueAttributes;
        $this->resource              = $resource;
        $this->helper                = $helper;
    }

    /**
     * Add attribute data to attribute data array
     *
     * @param string $tableName
     * @param array $data
     */
    public function addAttributeData($tableName, $data)
    {
        if (!empty($data['save'])) {
            foreach ($data['save'] as $dataItem) {
                $this->attributeData[$tableName]['save'][] = $dataItem;
            }
        }
        if (!empty($data['delete'])) {
            foreach ($data['delete'] as $dataItem) {
                $this->attributeData[$tableName]['delete'][] = $dataItem;
            }
        }
    }

    /**
     * Get attribute data array
     *
     * @return array
     */
    public function getAttributeData()
    {
        return $this->attributeData;
    }

    /**
     * Clear attribute data array
     */
    public function clearAttributeData()
    {
        $this->attributeData = [];
    }

    /**
     * Delete old data from attributes
     *
     * @param $collectedData
     * @param $entityType
     * @return void
     */
    public function deleteOldAttributeData($collectedData, $entityType)
    {
        foreach ($collectedData as $tableName => $dataArray) {
            if (empty($dataArray['delete'])) {
                continue;
            }
            foreach ($this->productAttributes->getData() as $attribute) {
                if ($tableName == $this->resource->getTableName($attribute->getTableName($entityType))) {
                    $attribute->deleteOldData($dataArray['delete']);
                }
            }
            foreach ($this->optionAttributes->getData() as $attribute) {
                if ($tableName == $this->resource->getTableName($attribute->getTableName($entityType))) {
                    $attribute->deleteOldData($dataArray['delete']);
                }
            }
            foreach ($this->optionValueAttributes->getData() as $attribute) {
                if ($tableName == $this->resource->getTableName($attribute->getTableName($entityType))) {
                    $attribute->deleteOldData($dataArray['delete']);
                }
            }
        }
    }

    /**
     * Set new options group option IDs
     *
     * @param int $groupOptionId
     * @return void
     */
    public function addNewGroupOptionIds($groupOptionId)
    {
        $this->newGroupOptionIds[] = $groupOptionId;
    }

    /**
     * Get new options group option IDs
     *
     * @return array
     */
    public function getNewGroupOptionIds()
    {
        return $this->newGroupOptionIds;
    }
}