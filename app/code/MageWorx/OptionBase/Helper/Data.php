<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Magento\Framework\Module\ModuleList;
use MageWorx\OptionBase\Model\ActionMode;

class Data extends AbstractHelper
{
    const CATALOG_PRICE_SCOPE = 'mageworx_apo/optionfeatures/add_plus_sign';

    /**
     * @var ActionMode
     */
    protected $actionMode;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var string|int
     */
    protected $moduleVersion;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ModuleList
     */
    protected $moduleList;

    /**
     * List of MageWorx Option attributes can be linked by SKU.
     *
     * @var array
     */
    protected $linkedAttributes = [];

    /**
     * @var array
     */
    protected $optionIdCache = [];

    /**
     * @var array
     */
    protected $optionTypeIdCache = [];

    /**
     * Path to config disable option value
     *
     * @var null
     */
    protected $isDisabledConfigPath = null;

    /**
     * Path to config enable visibility customer group
     *
     * @var null
     */
    protected $isEnabledVisibilityPerCustomerGroup = null;

    /**
     * Path to config enable visibility store view
     *
     * @var null
     */
    protected $isEnabledVisibilityPerStoreView = null;

    /**
     * Option Inventory Out Of Stock Options config path
     *
     * @deprecated
     * @var string
     */
    protected $configPathInventoryOutOfStockOptions;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param ModuleList $moduleList
     * @param JsonHelper $jsonHelper
     * @param ResourceConnection $resource
     * @param ActionMode $actionMode
     * @param array $linkedAttributes
     * @param null $isDisabledConfigPath
     * @param null $isEnabledVisibilityPerCustomerGroup
     * @param null $isEnabledVisibilityPerStoreView
     * @param string $configPathInventoryOutOfStockOptions
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response,
        ModuleList $moduleList,
        JsonHelper $jsonHelper,
        ActionMode $actionMode,
        ResourceConnection $resource,
        $linkedAttributes = [],
        $isDisabledConfigPath = null,
        $isEnabledVisibilityPerCustomerGroup = null,
        $isEnabledVisibilityPerStoreView = null,
        $configPathInventoryOutOfStockOptions = ''
    ) {
        $this->productMetadata                      = $productMetadata;
        $this->objectManager                        = $objectManager;
        $this->componentRegistrar                   = $componentRegistrar;
        $this->readFactory                          = $readFactory;
        $this->messageManager                       = $messageManager;
        $this->response                             = $response;
        $this->jsonHelper                           = $jsonHelper;
        $this->resource                             = $resource;
        $this->moduleList                           = $moduleList;
        $this->actionMode                           = $actionMode;
        $this->linkedAttributes                     = $linkedAttributes;
        $this->isDisabledConfigPath                 = $isDisabledConfigPath;
        $this->isEnabledVisibilityPerCustomerGroup  = $isEnabledVisibilityPerCustomerGroup;
        $this->isEnabledVisibilityPerStoreView      = $isEnabledVisibilityPerStoreView;
        $this->configPathInventoryOutOfStockOptions = $configPathInventoryOutOfStockOptions;
        parent::__construct($context);
    }

    /**
     * Convert option object/array data to specific array format
     * format: option data to $option[$optionId], value data to $option[$optionId]['values'][$valueId]
     *
     * @param array $options
     * @return array
     */
    public function beatifyOptions($options)
    {
        $array = [];
        if (empty($options)) {
            return $array;
        }

        foreach ($options as $optionKey => $option) {
            $array[$optionKey] = is_object($option) ? $option->getData() : $option;

            $values = [];
            if (isset($option['values'])) {
                $values = $option['values'];
            } elseif (is_object($option)) {
                $values = $option->getValues();
            }
            if (!$values) {
                continue;
            }
            foreach ($values as $valueKey => $value) {
                $array[$optionKey]['values'][$valueKey] = is_object($value) ? $value->getData() : $value;
            }
        }

        return $array;
    }

    /**
     * Search element of array by key and value
     *
     * @param string $key
     * @param string $value
     * @param array $array
     * @return string|null
     */
    public function searchArray($key, $value, $array)
    {
        foreach ($array as $k => $v) {
            if ($v[$key] === $value) {
                return $k;
            }
        }

        return null;
    }

    /**
     * Get options value qty based on the customers selection
     * Returns 1 by default
     *
     * @param $valueId
     * @param $valueData
     * @param QuoteItem $item
     * @param array $cart
     * @return float|int|mixed
     * @throws \Exception
     */
    public function getOptionValueQty($valueId, $valueData, QuoteItem $item, $cart = [])
    {
        if (empty($valueData['option_id'])) {
            throw new \Exception('Unable to locate the option id');
        }

        /** <!-- Change qty based on the customers input (qty input) --> */
        $itemQty       = $item->getQty() ? $item->getQty() : 1;
        $itemQty       = isset($cart[$item->getId()]) ? $cart[$item->getId()]['qty'] : $itemQty;
        $optionId      = $valueData['option_id'];
        $productOption = $item->getProduct()->getOptionById($optionId);
        $isOneTime     = (boolean)$productOption->getData('one_time');

        // Find base value's qty
        $valueQty       = 1;
        $infoBuyRequest = $this->getInfoBuyRequest($item->getProduct());
        if (!empty($infoBuyRequest['options_qty'][$optionId][$valueId])) {
            $valueQty = $infoBuyRequest['options_qty'][$optionId][$valueId];
        } elseif (!empty($infoBuyRequest['options_qty'][$optionId])
            && !is_array($infoBuyRequest['options_qty'][$optionId])
        ) {
            $valueQty = $infoBuyRequest['options_qty'][$optionId];
        }

        // Multiply quantity by quantity of a product if there is no one-time option
        if (!$isOneTime) {
            $valueQty *= $itemQty;
        }

        return $valueQty;
    }


    /**
     * @param string $class
     * @return string
     */
    public function getLinkField($class = ProductInterface::class)
    {
        $this->metadataPool = $this->objectManager->get('\Magento\Framework\EntityManager\MetadataPool');

        return (string)$this->metadataPool->getMetadata($class)->getLinkField();
    }

    /**
     * Check Magento edition.
     *
     * @return boolean
     */
    public function isEnterprise()
    {
        return strtolower($this->productMetadata->getEdition()) == 'enterprise'
            || strtolower($this->productMetadata->getEdition()) == 'b2b';
    }

    /**
     * Check if module is enabled
     *
     * @param $moduleName
     * @return array
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->moduleList->getOne($moduleName);
    }

    /**
     * @param $moduleName
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getModuleVersion($moduleName)
    {
        $path             = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead    = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile('composer.json');
        $data             = $this->jsonHelper->unserialize($composerJsonData);

        if ($data && is_array($data)) {
            return !empty($data['version']) ? $data['version'] : 0;
        }

        return !empty($data->version) ? $data->version : 0;
    }

    /**
     * Check module version according to conditions
     *
     * @param string $fromVersion
     * @param string $toVersion
     * @param string $fromOperator
     * @param string $toOperator
     * @param string $moduleName
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function checkModuleVersion(
        $fromVersion,
        $toVersion = '',
        $fromOperator = '>=',
        $toOperator = '<',
        $moduleName = 'Magento_Catalog'
    ) {
        if (empty($this->moduleVersion[$moduleName])) {
            $this->moduleVersion[$moduleName] = $this->getModuleVersion($moduleName);
        }

        $fromCondition = version_compare($this->moduleVersion[$moduleName], $fromVersion, $fromOperator);
        if ($toVersion === '') {
            return $fromCondition;
        }

        return $fromCondition && version_compare($this->moduleVersion[$moduleName], $toVersion, $toOperator);
    }

    /**
     * Return message about max_input_vars if form_key is not defined in request
     */
    public function checkMaxInputVars()
    {
        $data = $this->_getRequest()->getPostValue();
        if (!$data || !empty($data['form_key']) || ini_get('max_input_vars') >= 10000) {
            return;
        }

        if ($this->_getRequest()->getQuery('isAjax', false) || $this->_getRequest()->getQuery('ajax', false)) {
            $this->response->representJson(
                $this->jsonHelper->jsonEncode(
                    [
                        'error'   => true,
                        'message' => __('Invalid Form Key. Please try to set "max_input_vars" directive to "10000"')
                    ]
                )
            );
        } else {
            $this->messageManager->addWarningMessage('Please try to set "max_input_vars" directive to "10000"');
        }
    }

    /**
     * Clear id from all options and values.
     *
     * @param array $options
     * @return array
     */
    public function clearId($options)
    {
        foreach ($options as $oIndex => $option) {
            unset($options[$oIndex]['product_id']);

            $options[$oIndex]['record_id'] = $oIndex;
            unset($options[$oIndex]['option_id']);

            $values = isset($option['values']) ? $option['values'] : [];
            if (!$values) {
                continue;
            }

            foreach ($values as $vIndex => $value) {
                $options[$oIndex]['values'][$vIndex]['record_id'] = $vIndex;
                unset($options[$oIndex]['values'][$vIndex]['option_type_id']);
                unset($options[$oIndex]['values'][$vIndex]['option_id']);
            }
        }

        return $options;
    }

    /**
     * Convert id to the record id in every dependent value.
     * Usually used with the clearId($options) method.
     *
     * @param array $options
     * @return array
     */
    public function convertDependentIdToRecordId($options)
    {
        foreach ($options as $oIndex => $option) {
            $values = isset($option['values']) ? $option['values'] : [];

            if (!$values) {
                $dependencies = !empty($option['dependency'])
                    ? $this->jsonHelper->unserialize($option['dependency'])
                    : null;
                if ($dependencies) {
                    foreach ($dependencies as $dIndex => $dependency) {
                        $dependencies[$dIndex] = $this->replaceOptionIdWithRecordId($dependency, $options);
                    }
                    $options[$oIndex]['dependency'] = $this->jsonHelper->serialize($dependencies);
                }
                continue;
            }

            foreach ($values as $vIndex => $value) {
                $dependencies = !empty($value['dependency'])
                    ? $this->jsonHelper->unserialize($value['dependency'])
                    : null;

                if (!$dependencies) {
                    continue;
                }

                foreach ($dependencies as $dIndex => $dependency) {
                    $dependencies[$dIndex] = $this->replaceOptionIdWithRecordId($dependency, $options);
                }

                $values[$vIndex]['dependency'] = $this->jsonHelper->serialize($dependencies);
            }

            $options[$oIndex]['values'] = $values;
        }

        return $options;
    }

    /**
     * Replace id with record_id in the dependencies.
     *
     * @param array $dependency
     * @param array $options
     * @return array
     */
    private function replaceOptionIdWithRecordId($dependency, $options)
    {
        $dependencyOptionId = $dependency[0];
        $dependencyValueId  = $dependency[1];

        foreach ($options as $oIndex => $option) {
            $optionId = isset($option['option_id']) ? $option['option_id'] : '';
            if (!$optionId) {
                $optionId = isset($option['record_id']) ? $option['record_id'] : '';
            }

            if ($optionId != $dependencyOptionId) {
                continue;
            }

            $dependency[0] = $oIndex;

            $values = isset($option['values']) ? $option['values'] : [];
            foreach ($values as $vIndex => $value) {
                $valueId = isset($value['option_type_id']) ? $value['option_type_id'] : '';
                if (!$valueId) {
                    $valueId = isset($value['record_id']) ? $value['record_id'] : '';
                }

                if ($valueId != $dependencyValueId) {
                    continue;
                }

                $dependency[1] = $vIndex;
            }
        }

        return $dependency;
    }

    /**
     * Retrieve list of linked product attributes for OptionLink module.
     *
     * @param int|null $storeId
     * @return array
     */
    public function prepareLinkedAttributes($attributes)
    {
        $attributeName  = \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_NAME;
        $attributePrice = \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_PRICE;

        $this->linkedAttributes += [
            $attributeName  => $attributeName,
            $attributePrice => $attributePrice
        ];

        return array_intersect($this->linkedAttributes, $attributes);
    }

    /**
     * Get comparison part for WHERE condition
     * Checks amount of array elements and fill 'IN' or '=' condition with them
     *
     * @param array $data
     * @return string
     */
    public function getComparisonConditionPart(array $data)
    {
        if (!$data) {
            return " = 0";
        } elseif (count($data) === 1) {
            $value = !empty($data[0]) ? $data[0] : '0';

            return " = " . $value;
        } else {
            return " IN (" . implode(',', $data) . ")";
        }
    }

    /**
     * Get option type IDs from conditions for collection updaters
     *
     * @param array $conditions
     * @return array
     */
    public function findOptionTypeIdByConditions($conditions)
    {
        if (empty($conditions['option_id']) || !is_array($conditions['option_id'])) {
            return [];
        }

        $whereCondition = "option_id IN (" . implode(',', $conditions['option_id']) . ")";

        if (!empty($this->optionTypeIdCache[sha1($whereCondition)])) {
            return $this->optionTypeIdCache[sha1($whereCondition)];
        }

        $connection = $this->resource->getConnection();
        $sql        = $connection->select()
                                 ->from($this->getOptionValueTableName($conditions['entity_type']))
                                 ->reset(\Magento\Framework\DB\Select::COLUMNS)
                                 ->columns('option_type_id')
                                 ->distinct()
                                 ->where($whereCondition);

        $optionTypeIds                                  = $connection->fetchCol($sql);
        $this->optionTypeIdCache[sha1($whereCondition)] = $optionTypeIds;

        return $optionTypeIds;
    }

    /**
     * Reset option/option type IDs cache
     *
     * @return void
     */
    public function resetOptionIdsCache()
    {
        $this->optionTypeIdCache = [];
        $this->optionIdCache     = [];
    }

    /**
     * Get option IDs from conditions for collection updaters
     *
     * @param array $conditions
     * @return array
     */
    public function findOptionIdByConditions($conditions)
    {
        if (empty($conditions['option_id']) || !is_array($conditions['option_id'])) {
            return [];
        }

        return $conditions['option_id'];
    }

    /**
     * Get option value's table name by entity type
     *
     * @param string $entityType 'product' or 'group'
     * @return string
     */
    public function getOptionValueTableName($entityType)
    {
        if ($entityType == 'group') {
            return (string)$this->resource->getTableName('mageworx_optiontemplates_group_option_type_value');
        }

        return (string)$this->resource->getTableName('catalog_product_option_type_value');
    }

    /**
     * Get option's table name by entity type
     *
     * @param string $entityType 'product' or 'group'
     * @return string
     */
    public function getOptionTableName($entityType)
    {
        if ($entityType == 'group') {
            return (string)$this->resource->getTableName('mageworx_optiontemplates_group_option');
        }

        return (string)$this->resource->getTableName('catalog_product_option');
    }

    /**
     * Get info buy request from product
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getInfoBuyRequest($product)
    {
        $post = [];
        if (!$product) {
            return $post;
        }
        $infoBuyRequest = $product->getCustomOption('info_buyRequest');

        if (!$infoBuyRequest || !$infoBuyRequest->getValue()) {
            return $post;
        }

        return $this->decodeBuyRequestValue($infoBuyRequest->getValue());
    }

    /**
     * Encode buy request value
     *
     * @param string $value
     * @return array
     */
    public function decodeBuyRequestValue($value)
    {
        return $this->jsonDecode($value);
    }

    /**
     * Encode buy request value
     *
     * @param array $value
     * @return string
     */
    public function encodeBuyRequestValue($value)
    {
        return (string)$this->jsonEncode($value);
    }

    /**
     * Decode JSON securely
     *
     * @param string $value
     * @return array
     */
    public function jsonDecode($value)
    {
        return $this->jsonHelper->unserialize($value);
    }

    /**
     * Encode JSON securely
     *
     * @param array $value
     * @return string
     */
    public function jsonEncode($value)
    {
        return (string)$this->jsonHelper->serialize($value);
    }

    /**
     *
     * @param int $storeId
     * @return bool
     */
    public function isEnabledIsDisabled($storeId = null)
    {
        if (is_null($this->isDisabledConfigPath)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            $this->isDisabledConfigPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnabledVisibilityPerCustomerGroup($storeId = null)
    {
        if (is_null($this->isEnabledVisibilityPerCustomerGroup)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            $this->isEnabledVisibilityPerCustomerGroup,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnabledVisibilityPerCustomerStoreView($storeId = null)
    {
        if (is_null($this->isEnabledVisibilityPerStoreView)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            $this->isEnabledVisibilityPerStoreView,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get selectable option types
     *
     * @return array
     */
    public function getSelectableOptionTypes()
    {
        return [
            Option::OPTION_TYPE_DROP_DOWN,
            Option::OPTION_TYPE_RADIO,
            Option::OPTION_TYPE_CHECKBOX,
            Option::OPTION_TYPE_MULTIPLE
        ];
    }

    /**
     * Is selectable option type
     *
     * @param string $optionType
     * @return boolean
     */
    public function isSelectableOption($optionType)
    {
        return in_array($optionType, $this->getSelectableOptionTypes());
    }

    /**
     * Check if option is checkbox
     *
     * @param Option
     * @return bool
     */
    public function isCheckbox($option)
    {
        return $option->getType() == Option::OPTION_TYPE_CHECKBOX;
    }

    /**
     * Check if option is dropdown/swatch
     *
     * @param Option
     * @return bool
     */
    public function isDropdown($option)
    {
        return $option->getType() == Option::OPTION_TYPE_DROP_DOWN;
    }

    /**
     * Check if option is radio
     *
     * @param Option
     * @return bool
     */
    public function isRadio($option)
    {
        return $option->getType() == Option::OPTION_TYPE_RADIO;
    }

    /**
     * Check if option is multiselect
     *
     * @param Option
     * @return bool
     */
    public function isMultiselect($option)
    {
        return $option->getType() == Option::OPTION_TYPE_MULTIPLE;
    }

    /**
     * Check if catalog price scope is set to "Website"
     *
     * @return bool
     */
    public function isWebsiteCatalogPriceScope()
    {
        return (bool)$this->scopeConfig->getValue('catalog/price/scope');
    }

    /**
     * Check if inventory out of stock options are set to "Hide"
     *
     * @return bool
     */
    public function isHiddenOutOfStockOptions($storeId = null)
    {
        return false;
    }

    /**
     * Check if inventory out of stock options are set to "Disable"
     *
     * @return bool
     */
    public function isDisabledOutOfStockOptions($storeId = null)
    {
        return false;
    }

    /**
     * Check if it is running OptionImportExport module's import action
     *
     * @used to ignore APO config's disabling and import all features to avoid data loss
     *
     * @return bool
     */
    public function isAPOImportAction()
    {
        return $this->actionMode->getActionMode() === ActionMode::ACTION_IMPORT;
    }

    /**
     * Check if this is magento order create's configure quote items action
     *
     * @return boolean
     */
    public function isConfigureQuoteItemsAction()
    {
        return $this->_request->getFullActionName() === 'sales_order_create_configureQuoteItems';
    }

    /**
     * Check if this is magento checkout cart's configure quote items action
     *
     * @return boolean
     */
    public function isCheckoutCartConfigureAction()
    {
        return $this->_request->getFullActionName() === 'checkout_cart_configure';
    }

    /**
     * Check if this is product url with ShareableLink feature
     *
     * @return boolean
     */
    public function isShareableLink()
    {
        return $this->_request->getFullActionName() === 'catalog_product_view'
            && $this->_request->getParam('config');
    }

    /**
     * Get full action name
     *
     * @return string
     */
    public function getFullActionName()
    {
        return (string)$this->_request->getFullActionName();
    }

    /**
     * Convert character encoding
     *
     * @param $string
     * @return false|string|string[]|void|null
     */
    public function getConvertEncoding($string)
    {
        return mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
    }

    /**
     * Check if foreign key already exist
     *
     * @param array $item
     * @param string $tableName
     * @return int
     */
    public function isForeignKeyExist($item, $tableName)
    {
        $connection         = $this->resource->getConnection();
        $referenceTableName = $this->resource->getTableName($item['reference_table_name']);
        $skipFlag           = 0;

        if (!$connection->isTableExists($tableName) ||
            !$connection->isTableExists($referenceTableName) ||
            !$connection->tableColumnExists($tableName, $item['column_name']) ||
            !$connection->tableColumnExists($referenceTableName, $item['reference_column_name'])
        ) {
            $skipFlag = 1;

            return $skipFlag;
        }

        $fkList = $connection->getForeignKeys($tableName);
        foreach ($fkList as $fk) {
            if ($fk['TABLE_NAME'] == $tableName &&
                $fk['COLUMN_NAME'] == $item['column_name'] &&
                $fk['REF_TABLE_NAME'] == $referenceTableName &&
                $fk['REF_COLUMN_NAME'] == $item['reference_column_name']
            ) {
                $skipFlag = 1;
                break;
            }
        }

        return $skipFlag;
    }

    /**
     * @param string $sku
     * @return float
     */
    public function updateValueQtyToSalableQty(string $sku): float
    {
        $getSalableQuantityDataBySku = $this->objectManager->get(
            \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku::class
        );

        $stockInfo = $getSalableQuantityDataBySku->execute($sku);

        //@TODO
        return (float)$stockInfo[0]['qty'];
    }
}
