<?php
/**
 *
 * @package  Bdfc_General
 */
declare(strict_types=1);

namespace Bdfc\General\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class UnitPriceUpdate implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * Eav setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Attribute $eavAttribute
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeId = $this->eavAttribute->getIdByCode(Product::ENTITY, 'unit_price');

        $eavSetup->updateAttribute(Product::ENTITY, $attributeId, 'used_for_sort_by', 0, null);
        
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
