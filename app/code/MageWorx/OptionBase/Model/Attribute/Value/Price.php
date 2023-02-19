<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\Attribute\Value;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Store\Model\Store;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Model\OptionTypePrice;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Price extends AbstractAttribute
{
    const FIELD_MAGE_ONE_OPTIONS_IMPORT = '_custom_option_row_price';

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param ResourceConnection $resource
     * @param BaseHelper $baseHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param Serializer $serializer
     */
    public function __construct(
        ResourceConnection $resource,
        BaseHelper $baseHelper,
        DataObjectFactory $dataObjectFactory,
        Serializer $serializer
    ) {
        $this->serializer = $serializer;
        parent::__construct($resource, $baseHelper, $dataObjectFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return OptionTypePrice::KEY_MAGEWORX_OPTION_TYPE_PRICE;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOwnTable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($type = '')
    {
        $map = [
            'product' => OptionTypePrice::TABLE_NAME,
            'group'   => OptionTypePrice::OPTIONTEMPLATES_TABLE_NAME,
        ];
        if (!$type) {
            return $map[$this->entity->getType()];
        }

        return $map[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function collectData($entity, array $options)
    {
        $this->entity               = $entity;
        $currentStoreId             = (int)$entity->getDataObject()->getData('store_id') ?: 0;
        $isWebsiteCatalogPriceScope = $this->baseHelper->isWebsiteCatalogPriceScope();

        $savedItems = [];
        $items      = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[$this->getName()])) {
                    continue;
                }
                $savedItems[$value[OptionTypePrice::FIELD_OPTION_TYPE_ID]] = $value[$this->getName()];

                if (!$isWebsiteCatalogPriceScope && $currentStoreId !== Store::DEFAULT_STORE_ID) {
                    continue;
                }
                $items[$value[OptionTypePrice::FIELD_OPTION_TYPE_ID]] = [
                    OptionTypePrice::FIELD_PRICE      => $value[OptionTypePrice::FIELD_PRICE],
                    OptionTypePrice::FIELD_PRICE_TYPE => $value[OptionTypePrice::FIELD_PRICE_TYPE],
                    OptionTypePrice::FIELD_STORE_ID   => $currentStoreId
                ];
            }
        }

        return $this->collectPrices($items, $savedItems);
    }

    /**
     * Collect option value prices
     *
     * @param array $items
     * @param array $savedItems
     * @return array
     */
    protected function collectPrices($items, $savedItems)
    {
        $data = [];

        foreach ($savedItems as $savedItemKey => $savedItemValue) {
            $decodedJsonData = $savedItemValue ? $this->serializer->unserialize($savedItemValue) : null;

            if (empty($decodedJsonData)) {
                continue;
            }

            $data['delete'][] = [
                OptionTypePrice::FIELD_OPTION_TYPE_ID => $savedItemKey,
            ];
            $this->mergeNewPrices($decodedJsonData, $items, $savedItemKey);
            foreach ($decodedJsonData as $dataItem) {
                $data['save'][] = [
                    OptionTypePrice::FIELD_OPTION_TYPE_ID => $savedItemKey,
                    OptionTypePrice::FIELD_STORE_ID       => $dataItem[OptionTypePrice::FIELD_STORE_ID],
                    OptionTypePrice::FIELD_PRICE          => $dataItem[OptionTypePrice::FIELD_PRICE],
                    OptionTypePrice::FIELD_PRICE_TYPE     => $dataItem[OptionTypePrice::FIELD_PRICE_TYPE]
                ];
            }
        }

        return $data;
    }

    /**
     * Merge new prices with the old ones
     * Prepare data before save to db, used because we re-insert all prices for all store views
     *
     * @param array $decodedJsonData
     * @param array $items
     * @param int $savedItemKey
     */
    protected function mergeNewPrices(&$decodedJsonData, $items, $savedItemKey)
    {
        foreach ($items as $itemKey => $itemData) {
            if ($itemKey != $savedItemKey) {
                continue;
            }
            $storeId        = $itemData[OptionTypePrice::FIELD_STORE_ID];
            $storePrice     = $itemData[OptionTypePrice::FIELD_PRICE];
            $storePriceType = $itemData[OptionTypePrice::FIELD_PRICE_TYPE];
            if ($storePrice === '') {
                if (is_array($decodedJsonData) && isset($decodedJsonData[$storeId])) {
                    unset($decodedJsonData[$storeId]);
                }
                continue;
            }
            $isSaved = false;
            foreach ($decodedJsonData as $dataKey => $dataItem) {
                if ($dataItem[OptionTypePrice::FIELD_STORE_ID] == $storeId) {
                    $decodedJsonData[$dataKey][OptionTypePrice::FIELD_PRICE]      = $storePrice;
                    $decodedJsonData[$dataKey][OptionTypePrice::FIELD_PRICE_TYPE] = $storePriceType;
                    $isSaved                                                      = true;
                }
            }
            if ($isSaved) {
                continue;
            }
            $decodedJsonData[] = [
                OptionTypePrice::FIELD_STORE_ID   => $storeId,
                OptionTypePrice::FIELD_PRICE      => $storePrice,
                OptionTypePrice::FIELD_PRICE_TYPE => $storePriceType
            ];
        }
    }

    /**
     * Delete old mageworx option value prices
     *
     * @param array $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[OptionTypePrice::FIELD_OPTION_TYPE_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = OptionTypePrice::FIELD_OPTION_TYPE_ID . " IN (" . implode(',', $optionValueIds) . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [];
    }

    /**
     * Collect system data (customer group ids, store ids) from Magento 1 product csv
     *
     * @param array $systemData
     * @param array $productData
     * @param array $optionData
     * @param array $valueData
     */
    public function collectOptionsSystemDataMageOne(&$systemData, $productData, $optionData, $valueData = [])
    {
        if (empty($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
            || !is_array($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
        ) {
            return;
        }

        foreach ($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT] as $datumStore => $datumValue) {
            $systemData['store'][$datumStore] = $datumStore;
        }
    }

    /**
     * Collect system data (customer group ids, store ids) from Magento 2 template data
     *
     * @param array $data
     * @return array
     */
    public function collectTemplateSystemDataMageTwo($data)
    {
        return $this->collectStoresDataByKey($data, 'mageworx_option_price');
    }

    /**
     * Prepare data from Magento 1 product csv for future import
     *
     * @param array $systemData
     * @param array $productData
     * @param array $optionData
     * @param array $preparedOptionData
     * @param array $valueData
     * @param array $preparedValueData
     * @return void
     */
    public function prepareOptionsMageOne(
        $systemData,
        $productData,
        $optionData,
        &$preparedOptionData,
        $valueData = [],
        &$preparedValueData = []
    ) {
        if (empty($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
            || !is_array($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
        ) {
            return;
        }

        $mageworxPrice = [];
        foreach ($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT] as $datumStore => $datumValue) {
            if (!$this->hasStoreEquivalent($systemData, $datumStore)) {
                continue;
            }
            $priceType = substr($datumValue, -1) === '%'
                ? 'percent'
                : 'fixed';
            $price     = (float)rtrim($datumValue, '%');

            $mageworxPrice[] = [
                OptionTypePrice::FIELD_STORE_ID   => $systemData['map']['store'][$datumStore],
                OptionTypePrice::FIELD_PRICE      => $price,
                OptionTypePrice::FIELD_PRICE_TYPE => $priceType,
            ];
        }
        $preparedValueData[static::getName()] = $this->baseHelper->jsonEncode($mageworxPrice);
    }

    /**
     * Collect data for magento2 product export
     *
     * @param array $row
     * @param array $data
     * @return void
     */
    public function collectExportDataMageTwo(&$row, $data)
    {
        $prefix        = 'custom_option_row_';
        $attributeData = null;
        if (!empty($data[$this->getName()])) {
            $attributeData = $this->baseHelper->jsonDecode($data[$this->getName()]);
        }
        if (empty($attributeData) || !is_array($attributeData)) {
            $row[$prefix . $this->getName()] = null;

            return;
        }
        $result = [];
        foreach ($attributeData as $datum) {
            $parts = [];
            foreach ($datum as $datumKey => $datumValue) {
                $datumValue = $this->encodeSymbols($datumValue);
                $parts[]    = $datumKey . '=' . $datumValue . '';
            }
            $result[] = implode(',', $parts);
        }
        $row[$prefix . $this->getName()] = $result ? implode('|', $result) : null;
    }

    /**
     * Collect data for magento2 product import
     *
     * @param array $data
     * @return array|null
     */
    public function collectImportDataMageTwo($data)
    {
        if (!$this->hasOwnTable()) {
            return null;
        }

        if (!isset($data['custom_option_row_' . $this->getName()])) {
            return null;
        }

        $this->entity = $this->dataObjectFactory->create();
        $this->entity->setType('product');

        $prices       = [];
        $preparedData = [];
        $iterator     = 0;

        $attributeData = $data['custom_option_row_' . $this->getName()];
        if (empty($attributeData)) {
            return $this->collectPrices([], $prices);
        }

        $step1 = explode('|', $attributeData);
        foreach ($step1 as $step1Item) {
            $step2 = explode(',', $step1Item);
            foreach ($step2 as $step2Item) {
                $step3Item                              = explode('=', $step2Item);
                $step3Item[1]                           = $this->decodeSymbols($step3Item[1]);
                $preparedData[$iterator][$step3Item[0]] = $step3Item[1];
            }
            $iterator++;
        }
        $prices[$data['custom_option_row_id']] = $this->baseHelper->jsonEncode($preparedData);

        return $this->collectPrices([], $prices);
    }
}
