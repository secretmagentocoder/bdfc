<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecommage\CheckoutForm\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Config;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class add customer updated attribute to customer
 */
class AddCustomAttributeOrder implements DataPatchInterface
{

    private $eavSetupFactory;

    protected $categorySetupFactory;

    protected $quoteSetupFactory;

    protected $salesSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        Config $eavConfig,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute('order','customer_mobile_number',
        [
            'type' => 'varchar',
                'label' => 'Mobile Number',
                'input' => 'text',
                'required' => false,
                'sort_order' => 888,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
        ])
            ->addAttribute('order','customer_passport_no',[
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
            ])
            ->addAttribute('order','customer_nationality',[
                'type' => 'varchar',
                'label' => 'Nationality',
                'input' => 'text',
                'required' => false,
                'sort_order' => 888,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ])
            ->addAttribute('order','customer_country',[
                'type' => 'varchar',
                'label' => 'Country',
                'input' => 'text',
                'required' => false,
                'sort_order' => 888,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ])
                                                                                                                                                                ->addAttribute('order','customer_dob',[
                'type' => 'varchar',
                'label' => 'Date of birth',
                'input' => 'date',
                'required' => false,
                'sort_order' => 888,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ]);

        $this->moduleDataSetup->endSetup();
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
