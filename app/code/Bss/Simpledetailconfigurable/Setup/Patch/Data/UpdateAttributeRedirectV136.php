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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\Simpledetailconfigurable\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Data patch format
 */
class UpdateAttributeRedirectV136 implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Construct.
     *
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Add product attribute SDCP redirect.
     *
     * @return UpdateAttributeRedirectV136|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'only_display_product_page',
            [
                'group' => 'SDCP Redirect Hidden',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => __('Only display product page'),
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'class' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'is_used_in_grid' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'configurable',
                'sort_order' => 10,
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'redirect_to_configurable_product',
            [
                'group' => 'SDCP Redirect',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => __('Redirect to configurable product'),
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'class' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'is_used_in_grid' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,virtual,bundle,downloadable,grouped',
                'sort_order' => 10,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Compare ver module.
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.3.6';
    }
}
