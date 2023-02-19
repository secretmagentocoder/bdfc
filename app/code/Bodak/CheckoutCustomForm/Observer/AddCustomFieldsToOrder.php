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
use Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface;

/**
 * Class AddCustomFieldsToOrder
 *
 * @category Observer
 * @package  Bodak\CheckoutCustomForm\Observer
 */
class AddCustomFieldsToOrder implements ObserverInterface
{

    protected $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
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
        $quote = $this->checkoutSession->getQuote();

        $order->setData(
            CustomFieldsInterface::ARRIVAL_PICK_UP_BY_ANOTHER_PERSON,
            $quote->getData(CustomFieldsInterface::ARRIVAL_PICK_UP_BY_ANOTHER_PERSON)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_NAME,
            $quote->getData(CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_NAME)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_PHONE,
            $quote->getData(CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_PHONE)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_NUMBER_OF_CO_TRAVELLER,
            $quote->getData(CustomFieldsInterface::ARRIVAL_NUMBER_OF_CO_TRAVELLER)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_FULL_NAME,
            $quote->getData(CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_FULL_NAME)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_DOB,
            $quote->getData(CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_DOB)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_PACKAGE_ACKNOWLEDGE,
            $quote->getData(CustomFieldsInterface::ARRIVAL_PACKAGE_ACKNOWLEDGE)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_FLIGHT_DATE,
            $quote->getData(CustomFieldsInterface::ARRIVAL_FLIGHT_DATE)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_FLIGHT_TIME,
            $quote->getData(CustomFieldsInterface::ARRIVAL_FLIGHT_TIME)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_FLIGHT_NAME,
            $quote->getData(CustomFieldsInterface::ARRIVAL_FLIGHT_NAME)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_FLIGHT_NUMBER,
            $quote->getData(CustomFieldsInterface::ARRIVAL_FLIGHT_NUMBER)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_COLLECTION_TIME,
            $quote->getData(CustomFieldsInterface::ARRIVAL_COLLECTION_TIME)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_COLLECTION_POINT,
            $quote->getData(CustomFieldsInterface::ARRIVAL_COLLECTION_POINT)
        );
        $order->setData(
            CustomFieldsInterface::COLLECTION_DATE,
            $quote->getData(CustomFieldsInterface::COLLECTION_DATE)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND,
            $quote->getData(CustomFieldsInterface::ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_QUANTITY_ON_HAND,
            $quote->getData(CustomFieldsInterface::ARRIVAL_QUANTITY_ON_HAND)
        );
        $order->setData(
            CustomFieldsInterface::ARRIVAL_ALLOWANCE_LIMIT,
            $quote->getData(CustomFieldsInterface::ARRIVAL_ALLOWANCE_LIMIT)
        );
    }
}
