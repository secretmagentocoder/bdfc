<?php
namespace Sparsh\MobileNumberLogin\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Sparsh\MobileNumberLogin\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer's mobile number attribute.
     */
    const MOBILE_NUMBER = 'mobile_number';

    /**
     * Customer's mobile number country code.
     */
    const COUNTRY_CODE = 'country_code';

    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     *  Installs data.
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerSetup->addAttribute(Customer::ENTITY, self::MOBILE_NUMBER, [
            'label' => 'Mobile Number',
            'input' => 'text',
            'backend' => \Sparsh\MobileNumberLogin\Model\Attribute\Backend\MobileNumber::class,
            'required' => false,
            'sort_order' => 85,
            'position' => 85,
            'system' => false,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true
        ]);

        /** @var $attribute */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::MOBILE_NUMBER);

        $usedInForms = [
            'adminhtml_customer',
            'checkout_register',
            'customer_account_create',
            'customer_account_edit',
            'adminhtml_checkout'
        ];

        $attribute->setData('used_in_forms', $usedInForms);
        $attribute->save();

        $customerSetup->addAttribute(Customer::ENTITY, self::COUNTRY_CODE, [
            'label' => 'Country Code',
            'input' => 'text',
            'required' => false,
            'sort_order' => 84,
            'position' => 84,
            'system' => false,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false
        ]);

        /** @var $attribute */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::COUNTRY_CODE);

        $usedInForms = [
            'adminhtml_customer',
            'checkout_register',
            'customer_account_create',
            'customer_account_edit',
            'adminhtml_checkout'
        ];

        $attribute->setData('used_in_forms', $usedInForms);
        $attribute->save();

        $installer->endSetup();
    }
}
