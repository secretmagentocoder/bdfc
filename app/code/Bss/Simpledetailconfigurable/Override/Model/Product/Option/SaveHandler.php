<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Override\Model\Product\Option;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
use Magento\Catalog\Model\Product\Option\SaveHandler as CoreSaveHandler;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Catalog\Model\Product\Option;
use Bss\Simpledetailconfigurable\Helper\ModuleConfig;

class SaveHandler extends CoreSaveHandler
{
    /**
     * @var string[]
     */
    protected $compositeProductTypes = ['grouped', 'configurable', 'bundle'];

    /**
     * @var Relation
     */
    protected $relation;

    /**
     * @var ModuleConfig
     */
    protected $bssHelper;

    /**
     * @param OptionRepository $optionRepository
     * @param ModuleConfig $bssHelper
     * @param Relation $relation
     */
    public function __construct(
        OptionRepository $optionRepository,
        ModuleConfig $bssHelper,
        Relation $relation
    ) {
        $this->bssHelper = $bssHelper;
        $this->relation = $relation;
        parent::__construct($optionRepository);
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return ProductInterface|\Magento\Catalog\Api\Data\ProductInterface|object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getOptionsSaved()) {
            return $entity;
        }

        $options = $entity->getOptions();
        $optionIds = [];

        if ($options) {
            $optionIds = array_map(function (Option $option) {
                return $option->getOptionId();
            }, $options);
        }

        /** @var ProductInterface $entity */
        foreach ($this->optionRepository->getProductOptions($entity) as $option) {
            if (!in_array($option->getOptionId(), $optionIds)) {
                $this->optionRepository->delete($option);
            }
        }
        if ($options) {
            $this->processOptionsSaving($options, (bool)$entity->dataHasChangedFor('sku'), $entity);
        }

        return $entity;
    }

    /**
     * @param array $options
     * @param bool $hasChangedSku
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processOptionsSaving($options, $hasChangedSku, $product)
    {
        $magentoVer = $this->bssHelper->getMagentoVersion();
        if (version_compare($magentoVer, '2.4.3', '>=')) {
            $isProductHasRelations = $this->isProductHasRelations($product);
        }
        /** @var ProductCustomOptionInterface $option */
        foreach ($options as $option) {
            if (version_compare($magentoVer, '2.4.3', '>=')) {
                if (!$isProductHasRelations && $option->getIsRequire() && !$this->bssHelper->isModuleEnable()) {
                    $message = 'Required custom options cannot be added to a simple product'
                        . ' that is a part of a composite product.';
                    throw new CouldNotSaveException(__($message));
                }
            }

            if ($hasChangedSku && $option->hasData('product_sku')) {
                $option->setProductSku($product->getSku());
            }
            $this->optionRepository->save($option);
        }
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    protected function isProductHasRelations($product)
    {
        $result = true;
        if (!in_array($product->getId(), $this->compositeProductTypes)
            && $this->relation->getRelationsByChildren([$product->getId()])
        ) {
            $result = false;
        }

        return $result;
    }
}
