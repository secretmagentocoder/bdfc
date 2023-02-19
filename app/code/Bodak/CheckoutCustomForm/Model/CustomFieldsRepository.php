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
use Bodak\CheckoutCustomForm\Api\CustomFieldsRepositoryInterface;
use Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface;

/**
 * Class CustomFieldsRepository
 *
 * @category Model/Repository
 * @package  Bodak\CheckoutCustomForm\Model
 */
class CustomFieldsRepository implements CustomFieldsRepositoryInterface
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
     * CustomFieldsInterface
     *
     * @var CustomFieldsInterface
     */
    protected $customFields;

    /**
     * CustomFieldsRepository constructor.
     *
     * @param CartRepositoryInterface $cartRepository CartRepositoryInterface
     * @param ScopeConfigInterface    $scopeConfig    ScopeConfigInterface
     * @param CustomFieldsInterface   $customFields   CustomFieldsInterface
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ScopeConfigInterface $scopeConfig,
        CustomFieldsInterface $customFields
    ) {
        $this->cartRepository = $cartRepository;
        $this->scopeConfig    = $scopeConfig;
        $this->customFields   = $customFields;
    }
    /**
     * Save checkout custom fields
     *
     * @param int                                                      $cartId       Cart id
     * @param \Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface $customFields Custom fields
     *
     * @return \Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function saveCustomFields(
        int $cartId,
        CustomFieldsInterface $customFields
    ): CustomFieldsInterface {
        $cart = $this->cartRepository->getActive($cartId);
        if (!$cart->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 is empty', $cartId));
        }

        try {
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_PICK_UP_BY_ANOTHER_PERSON,
                $customFields->getArrivalPickUpByAnotherPerson()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_NAME,
                $customFields->getArrivalAnotherPersonName()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_PHONE,
                $customFields->getArrivalAnotherPersonPhone()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_NUMBER_OF_CO_TRAVELLER,
                $customFields->getArrivalNumberOfCoTraveller()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_FULL_NAME,
                $customFields->getArrivalCoTravellerFullName()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_DOB,
                $customFields->getArrivalCoTravellerDob()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_PACKAGE_ACKNOWLEDGE,
                $customFields->getArrivalPackageAcknowledge()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_FLIGHT_DATE,
                $customFields->getArrivalFlightDate()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_FLIGHT_TIME,
                $customFields->getArrivalFlightTime()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_FLIGHT_NUMBER,
                $customFields->getArrivalFlightNumber()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_FLIGHT_NAME,
                $customFields->getArrivalFlightName()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_COLLECTION_TIME,
                $customFields->getArrivalCollectionTime()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_COLLECTION_POINT,
                $customFields->getArrivalCollectionPoint()
            );
            $cart->setData(
                CustomFieldsInterface::COLLECTION_DATE,
                $customFields->getCollectionDate()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND,
                $customFields->getArrivalDoYouHaveQuantityOnHand()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_QUANTITY_ON_HAND,
                $customFields->getArrivalQuantityOnHand()
            );
            $cart->setData(
                CustomFieldsInterface::ARRIVAL_ALLOWANCE_LIMIT,
                $customFields->getArrivalAllowanceLimit()
            );

            $this->cartRepository->save($cart);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Custom order data could not be saved!'));
        }

        return $customFields;
    }

    /**
     * Get checkout custom fields by given order id
     *
     * @param Order $order Order
     *
     * @return CustomFieldsInterface
     * @throws NoSuchEntityException
     */
    public function getCustomFields(Order $order): CustomFieldsInterface
    {
        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order %1 does not exist', $order));
        }

        $this->customFields->setArrivalPickUpByAnotherPerson(
            $order->getData(CustomFieldsInterface::ARRIVAL_PICK_UP_BY_ANOTHER_PERSON)
        );
        $this->customFields->setArrivalAnotherPersonName(
            $order->getData(CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_NAME)
        );
        $this->customFields->setArrivalAnotherPersonPhone(
            $order->getData(CustomFieldsInterface::ARRIVAL_ANOTHER_PERSON_PHONE)
        );
        $this->customFields->setArrivalNumberOfCoTraveller(
            $order->getData(CustomFieldsInterface::ARRIVAL_NUMBER_OF_CO_TRAVELLER)
        );
        $this->customFields->setArrivalCoTravellerFullName(
            $order->getData(CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_FULL_NAME)
        );
        $this->customFields->setArrivalCoTravellerDob(
            $order->getData(CustomFieldsInterface::ARRIVAL_CO_TRAVELLER_DOB)
        );
        $this->customFields->setArrivalPackageAcknowledge(
            $order->getData(CustomFieldsInterface::ARRIVAL_PACKAGE_ACKNOWLEDGE)
        );
        $this->customFields->setArrivalFlightDate(
            $order->getData(CustomFieldsInterface::ARRIVAL_FLIGHT_DATE)
        );
        $this->customFields->setArrivalFlightTime(
            $order->getData(CustomFieldsInterface::ARRIVAL_FLIGHT_TIME)
        );
        $this->customFields->setArrivalFlightName(
            $order->getData(CustomFieldsInterface::ARRIVAL_FLIGHT_NAME)
        );
        $this->customFields->setArrivalFlightNumber(
            $order->getData(CustomFieldsInterface::ARRIVAL_FLIGHT_NUMBER)
        );
        $this->customFields->setArrivalCollectionTime(
            $order->getData(CustomFieldsInterface::ARRIVAL_COLLECTION_TIME)
        );
        $this->customFields->setArrivalCollectionPoint(
            $order->getData(CustomFieldsInterface::ARRIVAL_COLLECTION_POINT)
        );
        $this->customFields->setCollectionDate(
            $order->getData(CustomFieldsInterface::COLLECTION_DATE)
        );
        $this->customFields->setArrivalDoYouHaveQuantityOnHand(
            $order->getData(CustomFieldsInterface::ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND)
        );
        $this->customFields->setArrivalQuantityOnHand(
            $order->getData(CustomFieldsInterface::ARRIVAL_QUANTITY_ON_HAND)
        );
        $this->customFields->setArrivalAllowanceLimit(
            $order->getData(CustomFieldsInterface::ARRIVAL_ALLOWANCE_LIMIT)
        );

        return $this->customFields;
    }
}
