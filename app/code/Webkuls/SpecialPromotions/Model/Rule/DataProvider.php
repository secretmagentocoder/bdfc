<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */
namespace Webkuls\SpecialPromotions\Model\Rule;

use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\SalesRule\Model\Rule\Metadata\ValueProvider
     */
    protected $metadataValueProvider;

    /**
     * Initialize Dependencies
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $metadataValueProviderCore
     * @param \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider $metadataValueProvider
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $metadataValueProviderCore,
        \Webkuls\SpecialPromotions\Model\Rule\Metadata\ValueProvider $metadataValueProvider,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->coreRegistry = $registry;
        $this->metadataValueProvider = $metadataValueProvider;
        $meta = array_replace_recursive($this->getMetadataValues(), $meta);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get metadata values
     *
     * @return array
     */
    protected function getMetadataValues()
    {
        $rule = $this->coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        
        return $this->metadataValueProvider->getMetadataValues($rule);
    }

    /**
     * Get rule data values
     *
     * @return array | mixed
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Rule $rule */
        foreach ($items as $rule) {
            $rule = $this->getRule($rule, $rule->getId());
            $rule->setDiscountAmount($rule->getDiscountAmount() * 1);
            $rule->setDiscountQty($rule->getDiscountQty() * 1);

            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        return $this->loadedData;
    }

    /**
     * Get rule
     *
     * @param object $rule
     * @param integer $ruleId
     * @return Rule
     */
    private function getRule($rule, $ruleId)
    {
        return $rule->load($ruleId);
    }
}
