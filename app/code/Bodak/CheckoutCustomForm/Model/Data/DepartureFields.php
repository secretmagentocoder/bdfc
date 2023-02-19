<?php
/**
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Bodak\CheckoutCustomForm\Api\Data\DepartureFieldsInterface;

/**
 * Class DepartureFields
 *
 * @category Model/Data
 * @package  Bodak\CheckoutCustomForm\Model\Data
 */
class DepartureFields extends AbstractExtensibleObject implements DepartureFieldsInterface
{
    /**
     * Get DEPARTURE_SELECT_YOUR_LOUNGE
     *
     * @return string|null
     */
    public function getDepartureSelectYourLounge()
    {
        return $this->_get(self::DEPARTURE_SELECT_YOUR_LOUNGE);
    }

    /**
     * Get DEPARTURE_PICK_UP_BY_ANOTHER_PERSON
     *
     * @return string|null
     */
    public function getDeparturePickUpByAnotherPerson()
    {
        return $this->_get(self::DEPARTURE_PICK_UP_BY_ANOTHER_PERSON);
    }

    /**
     * Get DEPARTURE_ANOTHER_PERSON_NAME
     *
     * @return string|null
     */
    public function getDepartureAnotherPersonName()
    {
        return $this->_get(self::DEPARTURE_ANOTHER_PERSON_NAME);
    }

    /**
     * Get DEPARTURE_ANOTHER_PERSON_PHONE
     *
     * @return string|null
     */
    public function getDepartureAnotherPersonPhone()
    {
        return $this->_get(self::DEPARTURE_ANOTHER_PERSON_PHONE);
    }

    /**
     * Get DEPARTURE_PACKAGE_ACKNOWLEDGE
     *
     * @return string|null
     */
    public function getDeparturePackageAcknowledge()
    {
        return $this->_get(self::DEPARTURE_PACKAGE_ACKNOWLEDGE);
    }

    /**
     * Get DEPARTURE_FLIGHT_DATE
     *
     * @return string|null
     */
    public function getDepartureFlightDate()
    {
        return $this->_get(self::DEPARTURE_FLIGHT_DATE);
    }

    /**
     * Get DEPARTURE_FLIGHT_TIME
     *
     * @return string|null
     */
    public function getDepartureFlightTime()
    {
        return $this->_get(self::DEPARTURE_FLIGHT_TIME);
    }

    /**
     * Get DEPARTURE_FLIGHT_NAME
     *
     * @return string|null
     */
    public function getDepartureFlightName()
    {
        return $this->_get(self::DEPARTURE_FLIGHT_NAME);
    }

    /**
     * Get DEPARTURE_COLLECTION_TIME
     *
     * @return string|null
     */
    public function getDepartureCollectionTime()
    {
        return $this->_get(self::DEPARTURE_COLLECTION_TIME);
    }

    /**
     * Get DEPARTURE_FLIGHT_NUMBER
     *
     * @return string|null
     */
    public function getDepartureFlightNumber()
    {
        return $this->_get(self::DEPARTURE_FLIGHT_NUMBER);
    }

    /**
     * Get DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT
     *
     * @return string|null
     */
    public function getDepartureIsDirectOrConnectingFlight()
    {
        return $this->_get(self::DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT);
    }

    /**
     * Get DEPARTURE_DESTINATION
     *
     * @return string|null
     */
    public function getDepartureDestination()
    {
        return $this->_get(self::DEPARTURE_DESTINATION);
    }

    /**
     * Get DEPARTURE_CONNECTED_DESTINATION
     *
     * @return string|null
     */
    public function getDepartureConnectedDestination()
    {
        return $this->_get(self::DEPARTURE_CONNECTED_DESTINATION);
    }

    /**
     * Get DEPARTURE_ALLOWANCE_LIMIT
     *
     * @return string|null
     */
    public function getDepartureAllowanceLimit()
    {
        return $this->_get(self::DEPARTURE_ALLOWANCE_LIMIT);
    }


    /**
     * Set DEPARTURE_SELECT_YOUR_LOUNGE
     *
     * @param string|null $departureSelectYourLounge
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureSelectYourLounge(string $departureSelectYourLounge = null)
    {
        return $this->setData(self::DEPARTURE_SELECT_YOUR_LOUNGE, $departureSelectYourLounge);
    }

    /**
     * Set DEPARTURE_PICK_UP_BY_ANOTHER_PERSON
     *
     * @param string|null $departurePickUpByAnotherPerson
     *
     * @return DepartureFieldsInterface
     */
    public function setDeparturePickUpByAnotherPerson(string $departurePickUpByAnotherPerson = null)
    {
        return $this->setData(self::DEPARTURE_PICK_UP_BY_ANOTHER_PERSON, $departurePickUpByAnotherPerson);
    }

    /**
     * Set DEPARTURE_ANOTHER_PERSON_NAME
     *
     * @param string|null $departureAnotherPersonName
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureAnotherPersonName(string $departureAnotherPersonName = null)
    {
        return $this->setData(self::DEPARTURE_ANOTHER_PERSON_NAME, $departureAnotherPersonName);
    }

    /**
     * Set DEPARTURE_ANOTHER_PERSON_PHONE
     *
     * @param string|null $departureAnotherPersonPhone
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureAnotherPersonPhone(string $departureAnotherPersonPhone = null)
    {
        return $this->setData(self::DEPARTURE_ANOTHER_PERSON_PHONE, $departureAnotherPersonPhone);
    }

    /**
     * Set DEPARTURE_PACKAGE_ACKNOWLEDGE
     *
     * @param string|null $departurePackageAcknowledge
     *
     * @return DepartureFieldsInterface
     */
    public function setDeparturePackageAcknowledge(string $departurePackageAcknowledge = null)
    {
        return $this->setData(self::DEPARTURE_PACKAGE_ACKNOWLEDGE, $departurePackageAcknowledge);
    }

    /**
     * Set DEPARTURE_FLIGHT_DATE
     *
     * @param string|null $departureFlightDate
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureFlightDate(string $departureFlightDate = null)
    {
        return $this->setData(self::DEPARTURE_FLIGHT_DATE, $departureFlightDate);
    }

    /**
     * Set DEPARTURE_FLIGHT_TIME
     *
     * @param string|null $departureFlightTime
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureFlightTime(string $departureFlightTime = null)
    {
        return $this->setData(self::DEPARTURE_FLIGHT_TIME, $departureFlightTime);
    }

    /**
     * Set DEPARTURE_FLIGHT_NAME
     *
     * @param string|null $departureFlightName
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureFlightName(string $departureFlightName = null)
    {
        return $this->setData(self::DEPARTURE_FLIGHT_NAME, $departureFlightName);
    }

    /**
     * Set DEPARTURE_COLLECTION_TIME
     *
     * @param string|null $departureCollectionTime
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureCollectionTime(string $departureCollectionTime = null)
    {
        return $this->setData(self::DEPARTURE_COLLECTION_TIME, $departureCollectionTime);
    }

    /**
     * Set DEPARTURE_FLIGHT_NUMBER
     *
     * @param string|null $departureFlightNumber
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureFlightNumber(string $departureFlightNumber = null)
    {
        return $this->setData(self::DEPARTURE_FLIGHT_NUMBER, $departureFlightNumber);
    }

    /**
     * Set DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT
     *
     * @param string|null $departureIsDirectOrConnectingFlight
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureIsDirectOrConnectingFlight(string $departureIsDirectOrConnectingFlight = null)
    {
        return $this->setData(self::DEPARTURE_IS_DIRECT_OR_CONNECTING_FLIGHT, $departureIsDirectOrConnectingFlight);
    }

    /**
     * Set DEPARTURE_DESTINATION
     *
     * @param string|null $departureDestination
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureDestination(string $departureDestination = null)
    {
        return $this->setData(self::DEPARTURE_DESTINATION, $departureDestination);
    }

    /**
     * Set DEPARTURE_CONNECTED_DESTINATION
     *
     * @param string|null $departureConnectedDestination
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureConnectedDestination(string $departureConnectedDestination = null)
    {
        return $this->setData(self::DEPARTURE_CONNECTED_DESTINATION, $departureConnectedDestination);
    }

    /**
     * Set DEPARTURE_ALLOWANCE_LIMIT
     *
     * @param string|null $departureAllowanceLimit
     *
     * @return DepartureFieldsInterface
     */
    public function setDepartureAllowanceLimit(string $departureAllowanceLimit = null)
    {
        return $this->setData(self::DEPARTURE_ALLOWANCE_LIMIT, $departureAllowanceLimit);
    }
    
}
