<?php
/**
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Model\Order;
use Bodak\CheckoutCustomForm\Api\DepartureFieldsRepositoryInterface;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;

/**
 * Class DepartureFieldsRepository
 *
 * @category Model/Repository
 * @package  Bodak\CheckoutCustomForm\Model
 */
class DepartureFieldsRepository implements DepartureFieldsRepositoryInterface
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * DepartureFieldsInterface
     *
     * @var DepartureFieldsInterface
     */
    protected $departureFields;

    /**
     * DepartureFieldsRepository constructor.
     *
     * @param CartRepositoryInterface $cartRepository CartRepositoryInterface
     * @param ScopeConfigInterface    $scopeConfig    ScopeConfigInterface
     * @param DepartureFieldsInterface   $departureFields   DepartureFieldsInterface
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ScopeConfigInterface $scopeConfig,
        DepartureFieldsInterface $departureFields
    ) {
        $this->cartRepository = $cartRepository;
        $this->scopeConfig    = $scopeConfig;
        $this->departureFields   = $departureFields;
    }
    /**
     * Save checkout Departure fields
     *
     * @param int                                                      $cartId       Cart id
     * @param \Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface $departureFields Departure fields
     *
     * @return \Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function saveDepartureFields(
        int $cartId,
        DepartureFieldsInterface $departureFields
    ): DepartureFieldsInterface {
        $cart = $this->cartRepository->getActive($cartId);
        if (!$cart->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 is empty', $cartId));
        }

        try {
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_SELECT_YOUR_LOUNGE,
                $departureFields->getDepartureSelectYourLounge()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_PICK_UP_BY_ANOTHER_PERSON,
                $departureFields->getDeparturePickUpByAnotherPerson()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_NAME,
                $departureFields->getDepartureAnotherPersonName()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_PHONE,
                $departureFields->getDepartureAnotherPersonPhone()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_PACKAGE_ACKNOWLEDGE,
                $departureFields->getDeparturePackageAcknowledge()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_FLIGHT_DATE,
                $departureFields->getDepartureFlightDate()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_FLIGHT_TIME,
                $departureFields->getDepartureFlightTime()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_FLIGHT_NAME,
                $departureFields->getDepartureFlightName()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_COLLECTION_TIME,
                $departureFields->getDepartureCollectionTime()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_FLIGHT_NUMBER,
                $departureFields->getDepartureFlightNumber()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT,
                $departureFields->getDepartureIsDirectOrConnectingFlight()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_DESTINATION,
                $departureFields->getDepartureDestination()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_CONNECTED_DESTINATION,
                $departureFields->getDepartureConnectedDestination()
            );
            $cart->setData(
                DepartureFieldsInterface::DEPARTURE_ALLOWANCE_LIMIT,
                $departureFields->getDepartureAllowanceLimit()
            );

            $this->cartRepository->save($cart);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Departure order data could not be saved!'));
        }

        return $departureFields;
    }

    /**
     * Get checkout Departure fields by given order id
     *
     * @param Order $order Order
     *
     * @return DepartureFieldsInterface
     * @throws NoSuchEntityException
     */
    public function getDepartureFields(Order $order): DepartureFieldsInterface
    {
        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order %1 does not exist', $order));
        }

        $this->departureFields->setDepartureSelectYourLounge(
            $order->getData(DepartureFieldsInterface::DEPARTURE_SELECT_YOUR_LOUNGE)
        );
        $this->departureFields->setDeparturePickUpByAnotherPerson(
            $order->getData(DepartureFieldsInterface::DEPARTURE_PICK_UP_BY_ANOTHER_PERSON)
        );
        $this->departureFields->setDepartureAnotherPersonName(
            $order->getData(DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_NAME)
        );
        $this->departureFields->setDepartureAnotherPersonPhone(
            $order->getData(DepartureFieldsInterface::DEPARTURE_ANOTHER_PERSON_PHONE)
        );
        $this->departureFields->setDeparturePackageAcknowledge(
            $order->getData(DepartureFieldsInterface::DEPARTURE_PACKAGE_ACKNOWLEDGE)
        );
        $this->departureFields->setDepartureFlightDate(
            $order->getData(DepartureFieldsInterface::DEPARTURE_FLIGHT_DATE)
        );
        $this->departureFields->setDepartureFlightTime(
            $order->getData(DepartureFieldsInterface::DEPARTURE_FLIGHT_TIME)
        );
        $this->departureFields->setDepartureFlightName(
            $order->getData(DepartureFieldsInterface::DEPARTURE_FLIGHT_NAME)
        );
        $this->departureFields->setDepartureCollectionTime(
            $order->getData(DepartureFieldsInterface::DEPARTURE_COLLECTION_TIME)
        );
        $this->departureFields->setDepartureFlightNumber(
            $order->getData(DepartureFieldsInterface::DEPARTURE_FLIGHT_NUMBER)
        );
        $this->departureFields->setDepartureIsDirectOrConnectingFlight(
            $order->getData(DepartureFieldsInterface::DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT)
        );
        $this->departureFields->setDepartureDestination(
            $order->getData(DepartureFieldsInterface::DEPARTURE_DESTINATION)
        );
        $this->departureFields->setDepartureConnectedDestination(
            $order->getData(DepartureFieldsInterface::DEPARTURE_CONNECTED_DESTINATION)
        );
        $this->departureFields->setDepartureAllowanceLimit(
            $order->getData(DepartureFieldsInterface::DEPARTURE_ALLOWANCE_LIMIT)
        );

        return $this->departureFields;
    }
}
