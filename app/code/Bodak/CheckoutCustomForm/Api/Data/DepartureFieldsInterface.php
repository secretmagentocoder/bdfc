<?php
/**
 * Checkout Departure fields interface
 *
 * @package   Bodak\CheckoutDepartureForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Api\Data;

/**
 * Interface DepartureFieldsInterface
 *
 * @category Api/Data/Interface
 * @package  Bodak\CheckoutDepartureForm\Api\Data
 */
interface DepartureFieldsInterface
{
    const DEPARTURE_SELECT_YOUR_LOUNGE = 'departure_select_your_lounge';
    const DEPARTURE_PICK_UP_BY_ANOTHER_PERSON = 'departure_pick_up_by_another_person';
    const DEPARTURE_ANOTHER_PERSON_NAME = 'departure_another_person_name';
    const DEPARTURE_ANOTHER_PERSON_PHONE = 'departure_another_person_phone';
    const DEPARTURE_PACKAGE_ACKNOWLEDGE = 'departure_package_acknowledge';
    const DEPARTURE_FLIGHT_DATE = 'departure_flight_date';
    const DEPARTURE_FLIGHT_TIME = 'departure_flight_time';
    const DEPARTURE_FLIGHT_NAME = 'departure_flight_name';
    const DEPARTURE_COLLECTION_TIME = 'departure_collection_time';
    const DEPARTURE_FLIGHT_NUMBER = 'departure_flight_number';
    const DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT = 'departure_is_direct_or_connecting_flight';
    const DEPARTURE_DESTINATION = 'departure_destination';
    const DEPARTURE_CONNECTED_DESTINATION = 'departure_connected_destination';
    const DEPARTURE_ALLOWANCE_LIMIT = 'departure_allowance_limit';

    /**
     * Get DEPARTURE_SELECT_YOUR_LOUNGE
     *
     * @return string|null
     */
    public function getDepartureSelectYourLounge();

    /**
     * Get DEPARTURE_PICK_UP_BY_ANOTHER_PERSON
     *
     * @return string|null
     */
    public function getDeparturePickUpByAnotherPerson();

    /**
     * Get DEPARTURE_ANOTHER_PERSON_NAME
     *
     * @return string|null
     */
    public function getDepartureAnotherPersonName();

    /**
     * Get DEPARTURE_ANOTHER_PERSON_PHONE
     *
     * @return string|null
     */
    public function getDepartureAnotherPersonPhone();

    /**
     * Get DEPARTURE_PACKAGE_ACKNOWLEDGE
     *
     * @return string|null
     */
    public function getDeparturePackageAcknowledge();

    /**
     * Get DEPARTURE_FLIGHT_DATE
     *
     * @return string|null
     */
    public function getDepartureFlightDate();

    /**
     * Get DEPARTURE_FLIGHT_TIME
     *
     * @return string|null
     */
    public function getDepartureFlightTime();

    /**
     * Get DEPARTURE_FLIGHT_NAME
     *
     * @return string|null
     */
    public function getDepartureFlightName();

    /**
     * Get DEPARTURE_COLLECTION_TIME
     *
     * @return string|null
     */
    public function getDepartureCollectionTime();

    /**
     * Get DEPARTURE_FLIGHT_NUMBER
     *
     * @return string|null
     */
    public function getDepartureFlightNumber();

    /**
     * Get DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT
     *
     * @return string|null
     */
    public function getDepartureIsDirectOrConnectingFlight();

    /**
     * Get DEPARTURE_DESTINATION
     *
     * @return string|null
     */
    public function getDepartureDestination();

    /**
     * Get DEPARTURE_CONNECTED_DESTINATION
     *
     * @return string|null
     */
    public function getDepartureConnectedDestination();

    /**
     * Get DEPARTURE_ALLOWANCE_LIMIT
     *
     * @return string|null
     */
    public function getDepartureAllowanceLimit();


    /**
     * Set DEPARTURE_SELECT_YOUR_LOUNGE
     *
     * @param string|null $departureSelectYourLounge
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureSelectYourLounge(string $departureSelectYourLounge = null);

    /**
     * Set DEPARTURE_PICK_UP_BY_ANOTHER_PERSON
     *
     * @param string|null $departurePickUpByAnotherPerson
     *
     * @return DepartureFieldsInterface
     */
    public function setDeparturePickUpByAnotherPerson(string $departurePickUpByAnotherPerson = null);

    /**
     * Set DEPARTURE_ANOTHER_PERSON_NAME
     *
     * @param string|null $departureAnotherPersonName
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureAnotherPersonName(string $departureAnotherPersonName = null);

    /**
     * Set DEPARTURE_ANOTHER_PERSON_PHONE
     *
     * @param string|null $departureAnotherPersonPhone
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureAnotherPersonPhone(string $departureAnotherPersonPhone = null);

    /**
     * Set DEPARTURE_PACKAGE_ACKNOWLEDGE
     *
     * @param string|null $departurePackageAcknowledge
     *
     * @return DepartureFieldsInterface
     */
    public function setDeparturePackageAcknowledge(string $departurePackageAcknowledge = null);

    /**
     * Set DEPARTURE_FLIGHT_DATE
     *
     * @param string|null $departureFlightDate
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureFlightDate(string $departureFlightDate = null);

    /**
     * Set DEPARTURE_FLIGHT_TIME
     *
     * @param string|null $departureFlightTime
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureFlightTime(string $departureFlightTime = null);

    /**
     * Set DEPARTURE_FLIGHT_NAME
     *
     * @param string|null $departureFlightName
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureFlightName(string $departureFlightName = null);

    /**
     * Set DEPARTURE_COLLECTION_TIME
     *
     * @param string|null $departureCollectionTime
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureCollectionTime(string $departureCollectionTime = null);

    /**
     * Set DEPARTURE_FLIGHT_NUMBER
     *
     * @param string|null $departureFlightNumber
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureFlightNumber(string $departureFlightNumber = null);

    /**
     * Set DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT
     *
     * @param string|null $departureIsDirectOrConnectingFlight
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureIsDirectOrConnectingFlight(string $departureIsDirectOrConnectingFlight = null);

    /**
     * Set DEPARTURE_DESTINATION
     *
     * @param string|null $departureDestination
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureDestination(string $departureDestination = null);

    /**
     * Set DEPARTURE_CONNECTED_DESTINATION
     *
     * @param string|null $departureConnectedDestination
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureConnectedDestination(string $departureConnectedDestination = null);

    /**
     * Set DEPARTURE_ALLOWANCE_LIMIT
     *
     * @param string|null $departureAllowanceLimit
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureAllowanceLimit(string $departureAllowanceLimit = null);

}
