<?php
/**
 * Install custom checkout Data
 *
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;

/**
 * Class InstallData
 *
 * @category InstallData
 * @package  Bodak\CheckoutCustomForm\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * SalesSetupFactory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * QuoteSetupFactory
     *
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * InstallData constructor.
     *
     * @param SalesSetupFactory $salesSetupFactory SalesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory QuoteSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * Install data
     *
     * @param ModuleDataSetupInterface $setup   ModuleDataSetupInterface
     * @param ModuleContextInterface   $context ModuleContextInterface
     *
     * @return void
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $this->setup = $setup->startSetup();
        $this->installQuoteData();
        $this->installSalesData();
        $this->setup = $setup->endSetup();
    }

    /**
     * Install quote custom data
     *
     * @return void
     */
    public function installQuoteData()
    {
        $quoteInstaller = $this->quoteSetupFactory->create(
            [
                'resourceName' => 'quote_setup',
                'setup' => $this->setup
            ]
        );
        $quoteInstaller
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_PICK_UP_BY_ANOTHER_PERSON,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_NAME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_PHONE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_NUMBER_OF_CO_TRAVELLER,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_FULL_NAME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_DOB,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_PACKAGE_ACKNOWLEDGE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_FLIGHT_DATE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_FLIGHT_TIME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_FLIGHT_NUMBER,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_COLLECTION_TIME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_QUANTITY_ON_HAND,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                CustomFieldsInterface::ARRIVAL_ALLOWANCE_LIMIT,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )

            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_SELECT_YOUR_LOUNGE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_PICK_UP_BY_ANOTHER_PERSON,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_NAME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_PHONE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_PACKAGE_ACKNOWLEDGE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_FLIGHT_DATE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_FLIGHT_TIME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_COLLECTION_TIME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_FLIGHT_NUMBER,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_DESTINATION,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_CONNECTED_DESTINATION,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            )
            ->addAttribute(
                'quote',
                DepartureFieldsInterface::DEPARTURE_ALLOWANCE_LIMIT,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true]
            );
    }

    /**
     * Install sales custom data
     *
     * @return void
     */
    public function installSalesData()
    {
        $salesInstaller = $this->salesSetupFactory->create(
            [
                'resourceName' => 'sales_setup',
                'setup' => $this->setup
            ]
        );
        $salesInstaller
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_PICK_UP_BY_ANOTHER_PERSON,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_NAME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_PHONE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_NUMBER_OF_CO_TRAVELLER,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_FULL_NAME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_DOB,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_PACKAGE_ACKNOWLEDGE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_FLIGHT_DATE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_FLIGHT_TIME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_FLIGHT_NUMBER,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_COLLECTION_TIME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_QUANTITY_ON_HAND,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                CustomFieldsInterface::ARRIVAL_ALLOWANCE_LIMIT,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )

            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_SELECT_YOUR_LOUNGE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_PICK_UP_BY_ANOTHER_PERSON,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_NAME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_PHONE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_PACKAGE_ACKNOWLEDGE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_FLIGHT_DATE,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_FLIGHT_TIME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_COLLECTION_TIME,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_FLIGHT_NUMBER,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_DESTINATION,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_CONNECTED_DESTINATION,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            )
            ->addAttribute(
                'order',
                DepartureFieldsInterface::DEPARTURE_ALLOWANCE_LIMIT,
                ['type' => Table::TYPE_TEXT, 'length' => '255', 'nullable' => true, 'grid' => false]
            );
    }
}
