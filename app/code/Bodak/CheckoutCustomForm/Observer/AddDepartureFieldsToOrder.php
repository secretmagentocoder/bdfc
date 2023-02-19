<?php
/**
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;

/**
 * Class AddDepartureFieldsToOrder
 *
 * @category Observer
 * @package  Bodak\CheckoutCustomForm\Observer
 */
class AddDepartureFieldsToOrder implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Execute observer method.
     *
     * @param Observer $observer Observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $order->setData(
            DepartureFieldsInterface::DEPARTURE_SELECT_YOUR_LOUNGE,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_SELECT_YOUR_LOUNGE)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_PICK_UP_BY_ANOTHER_PERSON,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_PICK_UP_BY_ANOTHER_PERSON)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_NAME,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_NAME)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_PHONE,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_PHONE)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_PACKAGE_ACKNOWLEDGE,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_PACKAGE_ACKNOWLEDGE)
        );
        $departureDate = $quote->getData(DepartureFieldsInterface::DEPARTURE_FLIGHT_DATE);
        $dateModel = $this->dateTimeFactory->create();
        $departureDateFormat = $dateModel->gmtDate("Y-m-d", $departureDate);
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_FLIGHT_DATE,
            $departureDateFormat
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_FLIGHT_TIME,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_FLIGHT_TIME)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_FLIGHT_NAME,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_FLIGHT_NAME)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_COLLECTION_TIME,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_COLLECTION_TIME)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_FLIGHT_NUMBER,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_FLIGHT_NUMBER)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_DESTINATION,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_DESTINATION)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_CONNECTED_DESTINATION,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_CONNECTED_DESTINATION)
        );
        $order->setData(
            DepartureFieldsInterface::DEPARTURE_ALLOWANCE_LIMIT,
            $quote->getData(DepartureFieldsInterface::DEPARTURE_ALLOWANCE_LIMIT)
        );
    }
}
