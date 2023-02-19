<?php
/**
 * Checkout custom fields interface
 *
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Api\Data;

/**
 * Interface CustomFieldsInterface
 *
 * @category Api/Data/Interface
 * @package  Bodak\CheckoutCustomForm\Api\Data
 */
interface CustomFieldsInterface
{
    const ARRIVAL_PICK_UP_BY_ANOTHER_PERSON = 'arrival_pick_up_by_another_person';
    const ARRIVAL_ANOTHER_PERSON_NAME = 'arrival_another_person_name';
    const ARRIVAL_ANOTHER_PERSON_PHONE = 'arrival_another_person_phone';
    const ARRIVAL_NUMBER_OF_CO_TRAVELLER = 'arrival_number_of_co_traveller';
    const ARRIVAL_CO_TRAVELLER_FULL_NAME = 'arrival_co_traveller_full_name';
    const ARRIVAL_CO_TRAVELLER_DOB = 'arrival_co_traveller_dob';
    const ARRIVAL_PACKAGE_ACKNOWLEDGE = 'arrival_package_acknowledge';
    const ARRIVAL_FLIGHT_DATE = 'arrival_flight_date';
    const ARRIVAL_FLIGHT_TIME = 'arrival_flight_time';
    const ARRIVAL_FLIGHT_NUMBER = 'arrival_flight_number';
    const ARRIVAL_FLIGHT_NAME = 'arrival_flight_name';
    const ARRIVAL_COLLECTION_TIME = 'arrival_collection_time';
    const ARRIVAL_COLLECTION_POINT = 'arrival_collection_point';
    const ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND = 'arrival_do_you_have_quantity_on_hand';
    const ARRIVAL_QUANTITY_ON_HAND = 'arrival_quantity_on_hand';
    const ARRIVAL_ALLOWANCE_LIMIT = 'arrival_allowance_limit';
    const COLLECTION_DATE = 'collection_date';

    /**
     * Get ARRIVAL_PICK_UP_BY_ANOTHER_PERSON
     *
     * @return string|null
     */
    public function getArrivalPickUpByAnotherPerson();

    /**
     * Get ARRIVAL_ANOTHER_PERSON_NAME
     *
     * @return string|null
     */
    public function getArrivalAnotherPersonName();

    /**
     * Get ARRIVAL_ANOTHER_PERSON_PHONE
     *
     * @return string|null
     */
    public function getArrivalAnotherPersonPhone();

    /**
     * Get ARRIVAL_NUMBER_OF_CO_TRAVELLER
     *
     * @return string|null
     */
    public function getArrivalNumberOfCoTraveller();

    /**
     * Get ARRIVAL_CO_TRAVELLER_FULL_NAME
     *
     * @return string|null
     */
    public function getArrivalCoTravellerFullName();

    /**
     * Get ARRIVAL_CO_TRAVELLER_DOB
     *
     * @return string|null
     */
    public function getArrivalCoTravellerDob();

    /**
     * Get ARRIVAL_PACKAGE_ACKNOWLEDGE
     *
     * @return string|null
     */
    public function getArrivalPackageAcknowledge();

    /**
     * Get ARRIVAL_FLIGHT_DATE
     *
     * @return string|null
     */
    public function getArrivalFlightDate();

    /**
     * Get ARRIVAL_FLIGHT_TIME
     *
     * @return string|null
     */
    public function getArrivalFlightTime();

    /**
     * Get ARRIVAL_FLIGHT_NUMBER
     *
     * @return string|null
     */
    public function getArrivalFlightNumber();

    /**
     * Get ARRIVAL_FLIGHT_NAME
     *
     * @return string|null
     */
    public function getArrivalFlightName();

    /**
     * Get ARRIVAL_COLLECTION_TIME
     *
     * @return string|null
     */
    public function getArrivalCollectionTime();


    /**
     * Get ARRIVAL_COLLECTION_POINT
     *
     * @return string|null
     */
    public function getArrivalCollectionPoint();

    /**
     * Get COLLECTION_DATE
     *
     * @return string|null
     */
    public function getCollectionDate();

    /**
     * Get ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND
     *
     * @return string|null
     */
    public function getArrivalDoYouHaveQuantityOnHand();

    /**
     * Get ARRIVAL_QUANTITY_ON_HAND
     *
     * @return string|null
     */
    public function getArrivalQuantityOnHand();

    /**
     * Get ARRIVAL_ALLOWANCE_LIMIT
     *
     * @return string|null
     */
    public function getArrivalAllowanceLimit();


    /**
     * Set ARRIVAL_PICK_UP_BY_ANOTHER_PERSON
     *
     * @param string|null $arrivalPickUpByAnotherPerson
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalPickUpByAnotherPerson(string $arrivalPickUpByAnotherPerson = null);

    /**
     * Set ARRIVAL_ANOTHER_PERSON_NAME
     *
     * @param string|null $arrivalAnotherPersonName
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalAnotherPersonName(string $arrivalAnotherPersonName = null);

    /**
     * Set ARRIVAL_ANOTHER_PERSON_PHONE
     *
     * @param string|null $arrivalAnotherPersonPhone
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalAnotherPersonPhone(string $arrivalAnotherPersonPhone = null);

    /**
     * Set ARRIVAL_NUMBER_OF_CO_TRAVELLER
     *
     * @param string|null $arrivalNumberOfCoTraveller
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalNumberOfCoTraveller(string $arrivalNumberOfCoTraveller = null);

    /**
     * Set ARRIVAL_CO_TRAVELLER_FULL_NAME
     *
     * @param string|null $arrivalCoTravellerFullName
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalCoTravellerFullName(string $arrivalCoTravellerFullName = null);

    /**
     * Set ARRIVAL_CO_TRAVELLER_DOB
     *
     * @param string|null $arrivalCoTravellerDob
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalCoTravellerDob(string $arrivalCoTravellerDob = null);

    /**
     * Set ARRIVAL_PACKAGE_ACKNOWLEDGE
     *
     * @param string|null $arrivalPackageAcknowledge
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalPackageAcknowledge(string $arrivalPackageAcknowledge = null);

    /**
     * Set ARRIVAL_FLIGHT_DATE
     *
     * @param string|null $arrivalFlightDate
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalFlightDate(string $arrivalFlightDate = null);

    /**
     * Set ARRIVAL_FLIGHT_TIME
     *
     * @param string|null $arrivalFlightTime
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalFlightTime(string $arrivalFlightTime = null);

    /**
     * Set ARRIVAL_FLIGHT_NUMBER
     *
     * @param string|null $arrivalFlightNumber
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalFlightNumber(string $arrivalFlightNumber = null);

    /**
     * Set ARRIVAL_FLIGHT_NAME
     *
     * @param string|null $arrivalFlightName
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalFlightName(string $arrivalFlightName = null);

    /**
     * Set ARRIVAL_COLLECTION_TIME
     *
     * @param string|null $arrivalCollectionTime
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalCollectionTime(string $arrivalCollectionTime = null);


    /**
     * Set ARRIVAL_COLLECTION_POINT
     *
     * @param string|null $arrivalCollectionPoint
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalCollectionPoint(string $arrivalCollectionPoint = null);

    /**
     * Set COLLECTION_DATE
     *
     * @param string|null $collectionDate
     *
     * @return CustomFieldsInterface
     */
    public function setCollectionDate(string $collectionDate = null);

    /**
     * Set ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND
     *
     * @param string|null $arrivalDoYouHaveQuantityOnHand
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalDoYouHaveQuantityOnHand(string $arrivalDoYouHaveQuantityOnHand = null);

    /**
     * Set ARRIVAL_QUANTITY_ON_HAND
     *
     * @param string|null $arrivalQuantityOnHand
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalQuantityOnHand(string $arrivalQuantityOnHand = null);

    /**
     * Set ARRIVAL_ALLOWANCE_LIMIT
     *
     * @param string|null $arrivalAllowanceLimit
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalAllowanceLimit(string $arrivalAllowanceLimit = null);

}
