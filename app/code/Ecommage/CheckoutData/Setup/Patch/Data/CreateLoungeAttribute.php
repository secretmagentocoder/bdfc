<?php

declare(strict_types=1);

namespace Ecommage\CheckoutData\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

class CreateLoungeAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private  $_moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private  $_eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_moduleDataSetup = $moduleDataSetup;
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply the patch
     *
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_moduleDataSetup]);

        $eavSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'select_your_lounge', [
            'group' => 'Select Your Lounge',
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'sort_order' => 210,
            'label' => 'Select Your Lounge',
            'input' => 'select',
            'class' => '',
            'source' => 'Ecommage\CheckoutData\Model\Config\Source\LoungeAttributeOptions',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'apply_to' => ''
        ]);
    }

    /**
     * Get Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get alisas
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
