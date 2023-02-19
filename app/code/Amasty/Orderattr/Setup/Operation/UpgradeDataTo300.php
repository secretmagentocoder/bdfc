<?php

namespace Amasty\Orderattr\Setup\Operation;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Amasty\Orderattr\Model\Attribute\AttributeFactory;
use Amasty\Orderattr\Model\Attribute\Relation\RelationDetailsFactory;
use Amasty\Orderattr\Model\Attribute\Relation\RelationFactory;
use Amasty\Orderattr\Model\Attribute\Relation\RelationRepository;
use Amasty\Orderattr\Model\Attribute\Repository;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Value\Metadata\Form;
use Amasty\Orderattr\Model\Entity\Handler\Save;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Psr\Log\LoggerInterface;

class UpgradeDataTo300 {
    /**
     * LIMIT Order Values in one query
     */
    const VALUE_OFFSET = 1000;

    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var RelationRepository
     */
    private $relationRepository;

    /**
     * @var RelationFactory
     */
    private $relationFactory;

    /**
     * @var RelationDetailsFactory
     */
    private $detailsFactory;

    /**
     * @var int
     */
    private $valueOffset = 0;

    /**
     * @var Save
     */
    private $saveHandler;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var FormFactory
     */
    private $metadataFormFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $attributeIdsMapping = [];

    /**
     * @var array
     */
    private $optionsMapping = [];

    /**
     * @var array
     */
    private $attributesWithOptions = [];

    public function __construct(
        Repository $attributeRepository,
        AttributeFactory $attributeFactory,
        RelationRepository $relationRepository,
        RelationFactory $relationFactory,
        RelationDetailsFactory $detailsFactory,
        Save $saveHandler,
        EntityResolver $entityResolver,
        FormFactory $metadataFormFactory,
        State $state,
        LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeFactory = $attributeFactory;
        $this->relationRepository = $relationRepository;
        $this->relationFactory = $relationFactory;
        $this->detailsFactory = $detailsFactory;
        $this->saveHandler = $saveHandler;
        $this->entityResolver = $entityResolver;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function execute(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($currentAttributes = $this->getCurrentAttributes($setup)) {
            $this->saveAttributes($setup, $currentAttributes);

            $this->saveRelations($setup);

            $this->state->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                [$this, 'saveValues'],
                [$setup]
            );
        }

        $setup->getConnection()->update(
            $setup->getTable('eav_entity_type'),
            ['additional_attribute_table' => ''],
            ['entity_type_code = ?' => 'order']
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array $currentAttributes
     */
    protected function saveAttributes(ModuleDataSetupInterface $setup, $currentAttributes)
    {
        $mapper = [
            'is_used_in_grid' => 'show_on_grids',
            'save_selected' => 'save_to_future_checkout',
            'include_pdf' => 'include_in_pdf',
            'apply_default' => 'apply_default_value',
            'include_html_print_order' => 'include_in_html_print_order'
        ];

        foreach ($currentAttributes as $currentAttribute) {
            /** @var \Amasty\Orderattr\Model\Attribute\Attribute $attribute */
            $attribute = $this->attributeFactory->create();
            $optionsMappingTmp = [];

            foreach ([
                'attribute_code',
                'frontend_input',
                'frontend_label',
                'frontend_class',
                'is_required',
                'default_value',
                'is_visible_on_front',
                'is_visible_on_back',
                'sorting_order',
                'checkout_step',
                'is_used_in_grid',
                'save_selected',
                'include_pdf',
                'apply_default',
                'required_on_front_only',
                'include_html_print_order'
            ] as $key) {
                if (isset($currentAttribute[$key])) {
                    $attribute->setData(
                        isset($mapper[$key]) ? $mapper[$key] : $key,
                        $currentAttribute[$key]
                    );
                }
            }

            if (isset($currentAttribute['store_ids'])) {
                $attribute->setData('store_ids', explode(',', $currentAttribute['store_ids']));
            }

            if (isset($currentAttribute['customer_groups'])) {
                $attribute->setData('customer_groups', explode(',', $currentAttribute['customer_groups']));
            }

            if (!empty($currentAttribute['tooltip'])) {
                $attribute->setData(
                    'store_tooltips',
                    [0 => $currentAttribute['tooltip']]
                );
            }

            if ($attribute->getFrontendClass()) {
                /** setData to prevent serialize twice */
                switch ($attribute->getFrontendClass()) {
                    case 'validate-number':
                        $attribute->setData('validate_rules', ['input_validation' => 'numeric']);
                        break;
                    case 'validate-digits':
                        $attribute->setData('validate_rules', ['input_validation' => 'numeric']);
                        break;
                    case 'validate-email':
                        $attribute->setData('validate_rules', ['input_validation' => 'email']);
                        break;
                    case 'validate-url':
                        $attribute->setData('validate_rules', ['input_validation' => 'url']);
                        break;
                    case 'validate-alpha':
                        $attribute->setData('validate_rules', ['input_validation' => 'alpha']);
                        break;
                    case 'validate-alphanum':
                        $attribute->setData('validate_rules', ['input_validation' => 'alphanumeric']);
                        break;
                    case 'validate-length':
                        $attribute->setData(
                            'validate_rules',
                            ['max_text_length' => (int)$currentAttribute['validate_length_count']]
                        );
                        break;
                }

                $attribute->setFrontendClass(null);
            }

            $attributeLabels = $this->getCurrentAttributeLabels($setup, $currentAttribute['attribute_id']);
            if (!empty($attributeLabels)) {
                $newAttributeLabels = [];
                foreach ($attributeLabels as $label) {
                    $newAttributeLabels[$label['store_id']] = $label['value'];
                }
                $attribute->setStoreLabels($newAttributeLabels);
            }

            $attributeShippingMethods = $this->getCurrentAttributeShippingMethods(
                $setup,
                $currentAttribute['attribute_id']
            );

            if (!empty($attributeShippingMethods)) {
                $newShippingMethods = [];
                foreach ($attributeShippingMethods as $method) {
                    $newShippingMethods[] = $method['shipping_method'];
                }
                $attribute->setData('shipping_methods', $newShippingMethods);
            }

            $newAttributeOptions = [];
            if ($attribute->getInputTypeConfiguration()->isManageOptions()) {
                $options = $this->getCurrentAttributeOptions($setup, $currentAttribute['attribute_id']);
                if (!empty($options)) {
                    $optionsOrder = 0;
                    $newOptionsOrder = [];
                    foreach ($options as $option) {
                        if (!in_array($option['option_id'], $optionsMappingTmp)) {
                            $optionsMappingTmp[] = $option['option_id'];
                            $newOptionsOrder['option_' . $option['option_id']] = $optionsOrder++;
                        }

                        $newAttributeOptions['option_' . $option['option_id']]
                        [$option['store_id']] = $option['value'];
                    }
                    $attribute->setOption(['value' => $newAttributeOptions, 'order' => $newOptionsOrder]);
                }
                if (!empty($attribute->getDefaultValue())) {
                    $values = [];
                    foreach (explode(',', $attribute->getDefaultValue()) as $value) {
                        $values[] = 'option_' . $value;
                    }

                    $attribute->setDefault($values);
                }
            }

            try {
                $attribute = $this->attributeRepository->save($attribute);

                $this->attributeIdsMapping[$currentAttribute['attribute_id']] = $attribute->getAttributeId();
                if (!empty($newAttributeOptions)) {
                    $this->attributesWithOptions[] = $attribute->getAttributeCode();
                    foreach ($attribute->getSource()->getAllOptions(false) as $i => $newOption) {
                        $this->optionsMapping[$optionsMappingTmp[$i]] = $newOption['value'];
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical(
                    'Unable to save Order Attribute with previous ID '.(int)$currentAttribute['attribute_id'],
                    ['exception' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function saveRelations(ModuleDataSetupInterface $setup)
    {
        $relations = $this->getCurrentRelations($setup);
        if (!empty($relations)) {
            foreach ($relations as $relation) {
                /** @var \Amasty\Orderattr\Model\Attribute\Relation\Relation $newRelation */
                $newRelation = $this->relationFactory->create();
                $newRelation->setName($relation['name']);
                $details = $this->getCurrentRelationDetails($setup, $relation['relation_id']);
                if (!empty($details)) {
                    $newDetails = [];
                    foreach ($details as $detail) {
                        if (isset($this->attributeIdsMapping[$detail['attribute_id']])
                            && isset($this->attributeIdsMapping[$detail['dependent_attribute_id']])
                            && isset($this->optionsMapping[$detail['option_id']])
                        ) {
                            /** @var \Amasty\Orderattr\Model\Attribute\Relation\RelationDetails $newDetail */
                            $newDetail = $this->detailsFactory->create();
                            $newDetail->setAttributeId($this->attributeIdsMapping[$detail['attribute_id']]);
                            $newDetail->setDependentAttributeId(
                                $this->attributeIdsMapping[$detail['dependent_attribute_id']]
                            );
                            $newDetail->setOptionId($this->optionsMapping[$detail['option_id']]);
                            $newDetails[] = $newDetail;

                        }
                    }
                    $newRelation->setDetails($newDetails);
                }
                try {
                    $this->relationRepository->save($newRelation);
                } catch (\Exception $e) {
                    $this->logger->critical(
                        'Unable to save Order Relation with previous ID '.(int)$relation['relation_id'],
                        ['exception' => $e->getMessage()]
                    );
                }
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function saveValues(ModuleDataSetupInterface $setup)
    {
        while ($attributeValues = $this->getCurrentValues($setup)) {
            foreach ($attributeValues as $attributeValue) {
                $entity = false;
                if (!empty($attributeValue['order_entity_id'])) {
                    try {
                        $entity = $this->entityResolver->getEntityByOrderId((int)$attributeValue['order_entity_id']);
                    } catch (\Exception $e) {
                        $this->logger->critical(
                            'Unable to load Order '.(int)$attributeValue['order_entity_id'],
                            ['exception' => $e->getMessage()]
                        );
                        continue;
                    }
                } elseif (!empty($attributeValue['quote_id'])) {
                    $entity = $this->entityResolver->getEntityByQuoteId((int)$attributeValue['quote_id']);
                }
                if (!$entity) {
                    continue;
                }

                unset($attributeValue['id']);
                unset($attributeValue['customer_id']);
                unset($attributeValue['created_at']);
                unset($attributeValue['quote_id']);
                $attributeValue = array_filter(
                    $attributeValue,
                    function ($key) {
                        return !strpos($key, '_output');
                    },
                    ARRAY_FILTER_USE_KEY
                );

                $attributeValue = array_filter(
                    $attributeValue,
                    function ($value) {
                        return $value === null ? false : true;
                    }
                );

                foreach ($attributeValue as $key => &$attributeVal) {
                    if (in_array($key, $this->attributesWithOptions) && !empty($attributeVal)) {
                        $opts = explode(',', $attributeVal);
                        $attributeVal = [];
                        foreach ($opts as $opt) {
                            if (isset($this->optionsMapping[$opt])) {
                                $attributeVal[] = $this->optionsMapping[$opt];
                            }
                        }
                    }
                }
                try {
                    $form = $this->createEntityForm($entity, 'all_attributes');
                    $form->setInvisibleIgnored(false);
                    $request = $form->prepareRequest($attributeValue);
                    $data = $form->extractData($request);
                    $form->restoreData($data);
                    $this->saveHandler->execute($entity);
                } catch (\Exception $e) {
                    $this->logger->critical(
                        'Unable to save Order Attribute Values For '
                        . (($entity->getParentEntityType() == CheckoutEntityInterface::ENTITY_TYPE_QUOTE)
                            ? 'Quote' : 'Order')
                        . ' ' . $entity->getParentId(),
                        ['exception' => $e->getMessage()]
                    );
                }
            }
            $this->valueOffset += self::VALUE_OFFSET;
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @return array
     */
    protected function getCurrentAttributes(ModuleDataSetupInterface $setup)
    {
        return $setup->getConnection()->fetchAll(
            $setup->getConnection()->select()
                ->from(['eavAttribute' => $setup->getTable('eav_attribute')])
                ->joinInner(
                    ['amastyAttribute' => $setup->getTable('amasty_orderattr_order_eav_attribute')],
                    '`eavAttribute`.`attribute_id` = `amastyAttribute`.`attribute_id`'
                )
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @return array
     */
    protected function getCurrentRelations(ModuleDataSetupInterface $setup)
    {
        return $setup->getConnection()->fetchAll(
            $setup->getConnection()->select()
                ->from(['relations' => $setup->getTable('amasty_orderattr_attributes_relation')])
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param int $relationId
     *
     * @return array
     */
    protected function getCurrentRelationDetails(ModuleDataSetupInterface $setup, $relationId)
    {
        return $setup->getConnection()->fetchAll(
            $setup->getConnection()->select()
                ->from(['relationDetails' => $setup->getTable('amasty_orderattr_attributes_relation_details')])
                ->where('`relationDetails`.`relation_id` = ?', (int)$relationId)
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param int $attributeId
     *
     * @return array
     */
    protected function getCurrentAttributeLabels(ModuleDataSetupInterface $setup, $attributeId)
    {
        return $setup->getConnection()->fetchAll(
            $setup->getConnection()->select()
                ->from(['attributeLabels' => $setup->getTable('eav_attribute_label')])
                ->where('`attributeLabels`.`attribute_id` = ?', (int) $attributeId)
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param int $attributeId
     *
     * @return array
     */
    protected function getCurrentAttributeShippingMethods(ModuleDataSetupInterface $setup, $attributeId)
    {
        return $setup->getConnection()->fetchAll(
            $setup->getConnection()->select()
                ->from(['attributeShippingMethods' => $setup->getTable('amasty_orderattr_shipping_methods')])
                ->where('`attributeShippingMethods`.`attribute_id` = ?', (int) $attributeId)
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param int $attributeId
     *
     * @return array
     */
    protected function getCurrentAttributeOptions(ModuleDataSetupInterface $setup, $attributeId)
    {
        return $setup->getConnection()->fetchAll(
            $setup->getConnection()->select()
                ->from(['attributeOptions' => $setup->getTable('eav_attribute_option')])
                ->join(
                    ['attributeOptionsValues' => $setup->getTable('eav_attribute_option_value')],
                    '`attributeOptions`.`option_id` = `attributeOptionsValues`.`option_id`'
                )
                ->where('`attributeOptions`.`attribute_id` = ?', (int) $attributeId)
                ->order('attributeOptions.sort_order ASC')
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @return array
     */
    protected function getCurrentValues(ModuleDataSetupInterface $setup)
    {
        return $setup->getConnection()->fetchAll(
            $setup->getConnection()->select()
                ->from(['attributeValues' => $setup->getTable('amasty_orderattr_order_attribute_value')])
                ->limit(self::VALUE_OFFSET, $this->valueOffset)
        );
    }

    /**
     * Return Checkout Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @param string $checkoutFormCode
     *
     * @return Form
     */
    protected function createEntityForm($entity, $checkoutFormCode)
    {
        /** @var Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode($checkoutFormCode)
            ->setEntity($entity);

        return $formProcessor;
    }
}
