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
use Magento\Customer\Model\Customer;

class ValidationMobileNumber implements DataPatchInterface, PatchRevertableInterface
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
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(Customer::ENTITY, 'mobile_number', 'is_required', 1);
        
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [   
        ];
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
