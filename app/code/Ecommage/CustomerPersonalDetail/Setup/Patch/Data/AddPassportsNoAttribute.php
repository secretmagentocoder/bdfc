<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommage\CustomerPersonalDetail\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Config;

/**
 * Class add customer updated attribute to customer
 */
class AddPassportsNoAttribute implements DataPatchInterface
{

    private $eavConfig;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'passport_no',
            [
                'type' => 'varchar',
                'label' => 'Passport No',
                'input' => 'text',
                'required' => false,
                'sort_order' => 888,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ]
        );


        $sampleAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'passport_no');
        $sampleAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer']
        );
        $sampleAttribute->save();

        return $this;
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
    public function getAliases()
    {
        return [];
    }
}
