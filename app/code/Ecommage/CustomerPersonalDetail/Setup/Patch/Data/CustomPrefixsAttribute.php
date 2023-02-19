<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommage\CustomerPersonalDetail\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class add non specified gender attribute option to customer
 */
class CustomPrefixsAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    private $eavConfig;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface                                                   $moduleDataSetup,
        CustomerSetupFactory                                                       $customerSetupFactory,
        \Magento\Eav\Model\Config                                                  $eavConfig
    )
    {
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
            'prefix',
            [
                'label' => 'Prefix Name',
                'input' => 'select',
                'type' => 'text',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'required' => false,
                'user_defined' => true,
                'position' => 666,
                'visible' => true,
                'system' => false,
                'backend' => '',
                'option' => [
                    'values' => [
                            1=>'Mr',
                            2=>'Mrs'
                        ]
                ],
            ]
        );

        $sampleAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'prefix');
        $sampleAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer']
        );
        $sampleAttribute->save();

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
    public static function getVersion()
    {
        return '2.0.2';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
